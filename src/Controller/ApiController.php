<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\wecom\callback\Prpcrypt;
use App\wecom\callback\WXBizMsgCrypt;
use Alzee\Qstamp\Qstamp;
use Alzee\Fwc\Fwc;
use Alzee\Fwc\Contacts;
use Alzee\Fwc\Approval;
use Alzee\Fwc\Message;
use Alzee\Fwc\Media;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Device;
use App\Entity\Fingerprint;
use App\Entity\Organization;
use App\Entity\Wecom;
use Psr\Log\LoggerInterface;

#[Route('/api')]
class ApiController extends AbstractController
{
    private $doctrine;
    private $logger;

    public function __construct(ManagerRegistry $doctrine, LoggerInterface $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_api')]
    public function index(): Response
    {
        return new Response('<body></body>');
    }

    #[Route('/wecom/callback/{corpId}', name: 'app_wecom_callback')]
    public function wecom($corpId, Request $request): Response
    {
        $query = $request->query;
        $msg_signature= $query->get('msg_signature');
        $timestamp = $query->get('timestamp');
        $nonce = $query->get('nonce');
        $postData = $request->getContent();

        if ($postData) {
            $str = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA)->Encrypt;
            $str1 = '';
        } else {
            $str = $query->get('echostr');
            $str1 = $str;
        }
        $wecom = $this->doctrine->getRepository(Wecom::class)->findOneByCorpId($corpId);

        $approval_token = $wecom->getCallbackToken();
        $encodingAesKey = $wecom->getCallbackAESKey();

        $wxcpt = new WXBizMsgCrypt($approval_token, $encodingAesKey, $corpId);
        $errCode = $wxcpt->VerifyURL($msg_signature, $timestamp, $nonce, $str, $str1);
        if ($errCode == 0) {
            if ($postData) {
                $pc = new Prpcrypt($encodingAesKey);
                $arr = $pc->decrypt($str, $corpId);
                $data = simplexml_load_string($arr[1], 'SimpleXMLElement', LIBXML_NOCDATA);
                // dump($data);

                $contacts = new Contacts($this->getWecomTokenFromCache($corpId, 'contacts'));
                $approval = new Approval($this->getWecomTokenFromCache($corpId, 'app'));

                $device = $this->doctrine->getRepository(Device::class)->findOneByOrg($wecom->getOrg());

                $uuid = $device->getUuid();
                $stamp = new Qstamp($uuid, $this->getStampTokenFromCache($uuid));
                if ($data->Event == 'sys_approval_change' && (string)$data->ApprovalInfo->StatuChangeEvent === "2") {
                    $username = (string)$data->ApprovalInfo->Applyer->UserId;
                    $fpr = $this->doctrine->getRepository(Fingerprint::class)->findOneByDeviceAndUsername($device, $username);
                    $spNo = (string)$data->ApprovalInfo->SpNo;
                    switch ((string)$data->ApprovalInfo->TemplateId) {
                        case $wecom->getStampingTemplateId():
                            if (is_null($fpr)){
                                $this->logger->error('fingerprint not found -- device: {device}, username: {username}',
                                    [
                                        'device' => $device,
                                        'username' => $username,
                                    ]
                                );
                            }
                            $stamp->pushApplication($stamp->applicationIdFromWecom($spNo), $fpr->getId(), $approval->getFieldValue($spNo, '用章次数'));
                            break;
                        case $wecom->getAddingFprTemplateId():
                            if (is_null($fpr)) {
                                $cache = new RedisAdapter(RedisAdapter::createConnection('redis://localhost'));
                                if ($cache->getItem("STAMP_ADDING_FPR_{$username}")->isHit()) break;

                                $cache->get("STAMP_ADDING_FPR_{$username}", function (ItemInterface $item){
                                    $item->expiresAfter(2);
                                    return true;
                                });
                                
                                $em = $this->doctrine->getManager();
                                $fpr = new Fingerprint();
                                $fpr->setUsername($username);
                                $fpr->setOrg($wecom->getOrg());
                                $fpr->setDevice($device);
                                $em->persist($fpr);
                                $em->flush();
                            }
                            $stamp->addFingerprint($fpr->getId(), $username);
                            break;
                    }
                }

                if ($data->Event == 'change_contact' && $data->ChangeType == 'update_tag' && $data->TagId == $device->getTagId() && $data->DelUserItems) {
                    foreach (explode(',', $data->DelUserItems) as $user) {
                        $fpr = $this->doctrine->getRepository(Fingerprint::class)->findOneByDeviceAndUsername($device, $user);
                        if (! is_null($fpr)) {
                            $stamp->delFingerprint($fpr->getId());
                        }
                    }
                }
            }
            echo $str1;
        } else {
            print("ERR: " . $errCode . "\n\n");
        }

        return new Response('');
    }

    #[Route('/qstamp/callback', name: 'app_qstamp_callback')]
    public function qstamp(Request $request): Response
    {
        $data = $request->getContent();
        $data = stripcslashes($data);
        $data = stripcslashes($data);
        $data = str_replace('"{', '{', $data);
        $data = str_replace('}"', '}', $data);
        $data = json_decode($data);
        $uuid = $data->uuid;
        $stamp = new Qstamp($uuid, $this->getStampTokenFromCache($uuid));
        $device = $this->doctrine->getRepository(Device::class)->findOneByUUID($uuid);
        $wecom = $this->doctrine->getRepository(Wecom::class)->findOneByOrg($device->getOrg());
        $corpId = $wecom->getCorpId();
        // dump($data);
        switch ($data->cmd) {
            case 1000:  // startup
                $stamp->setSleepTime(30);
                break;
            case 1010:  // fingerprint added
                $cache = new RedisAdapter(RedisAdapter::createConnection('redis://localhost'));
                if ($cache->getItem("STAMP_{$uuid}_JUST_CALLED")->isHit()) break;

                $cache->get("STAMP_{$uuid}_JUST_CALLED", function (ItemInterface $item){
                    $item->expiresAfter(2);
                    return true;
                });

                $uid = $data->data->userId;
                $fpr = $this->doctrine->getRepository(Fingerprint::class)->find($uid);
                if (is_null($fpr)) break;
                if ($data->data->status) {
                    $contacts = new Contacts($this->getWecomTokenFromCache($corpId, 'contacts'));
                    $contacts->addUsersToTag($device->getTagId(), [$fpr->getUsername()]);
                } else {
                    // try again if failed, loop until success or press OK button on device to abort
                    $stamp->addFingerprint($uid, $fpr->getUsername());
                }
                break;
            case 1130:  // img uploaded
                // TODO and device->oa = 'wecom';
                if (!isset($data->data->applicationId)) break;
                // Upload to wecom
                $path = $_ENV['IMG_DIR_PREFIX'] . preg_replace('/\/group\d+/', '', $data->data->path);
                $media = new Media($this->getWecomTokenFromCache($corpId, 'app'));
                $mediaId = $media->upload($path, 'image')->media_id;

                // Send images message to wecom chat
                $approval = new Approval($this->getWecomTokenFromCache($corpId, 'app'));
                $spNo = $stamp->applicationIdToWecom($data->data->applicationId);
                $applicant = $approval->getApplicant($spNo);
                $approver = $approval->getApprovers($spNo);
                $msg = new Message($this->getWecomTokenFromCache($corpId, 'app'));
                $agentId = $wecom->getAppid();
                // $data = $msg->sendTextTo("$applicant|$approver", "test", '3010040');
                $data = $msg->sendImgTo("$applicant|$approver", $mediaId, $agentId);
                break;
        }
        return $this->json(["code" => 0, "msg" => '', "data" => ""]);
    }

    /**
     * @param string $corpId
     * @param string $app 'contacts | approval'
     *
     * @return string
     *
     */
    public function getWecomTokenFromCache($corpId, $app, $refresh = false)
    {
        $cache = new RedisAdapter(RedisAdapter::createConnection('redis://localhost'));

        if ($refresh) {
            $cache->clear("WECOM_{$corpId}_{$app}_TOKEN");
        }

        $token = $cache->get("WECOM_{$corpId}_{$app}_TOKEN", function (ItemInterface $item) use ($corpId, $app) {
            $item->expiresAfter(7200);
            $wecom = $this->doctrine->getRepository(Wecom::class)->findOneByCorpId($corpId);
            $fwc = new Fwc();
            $secret = match ($app) {
                'contacts' => $wecom->getContactsSecret(),
                'approval' => $wecom->getApprovalSecret(),
                'app' => $wecom->getAppsecret(),
            };
            return $fwc->getAccessToken($corpId, $secret);
        });

        return $token;
    }

    public function getStampTokenFromCache($uuid, $refresh = false)
    {
        $cache = new RedisAdapter(RedisAdapter::createConnection('redis://localhost'));

        if ($refresh) {
            $cache->clear("STAMP_{$uuid}_TOKEN");
        }

        $token = $cache->get("STAMP_{$uuid}_TOKEN", function (ItemInterface $item) use ($uuid) {
            $item->expiresAfter(7200);

            $stamp = new Qstamp($uuid);
            $key = $_ENV["STAMP_APP_KEY"];
            $secret = $_ENV["STAMP_APP_SECRET"];
            return $stamp->getToken($key, $secret);
        });

        return $token;
    }

    #[Route('/test')]
    public function test(Request $request){
        $device = $this->doctrine->getRepository(Device::class)->find(2);
        $username = 'Bayue1';
        $fpr = $this->doctrine->getRepository(Fingerprint::class)->findOneByDeviceAndUsername($device, $username);
        if (is_null($fpr)){
            $this->logger->error('fingerprint not found -- device: {device}, username: {username}',
                [
                    'device' => $device,
                    'username' => $username,
                ]
            );
        }
        dump($fpr);
        return new Response('<body></body>');
    }
}
