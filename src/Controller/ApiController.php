<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use App\wecom\callback\Prpcrypt;
use App\wecom\callback\WXBizMsgCrypt;
use Alzee\Qstamp\Qstamp;
use Alzee\Fwc\Fwc;
use Alzee\Fwc\Contacts;
use Alzee\Fwc\Approval;
use Alzee\Fwc\Message;
use Alzee\Fwc\Media;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/api')]
class ApiController extends AbstractController
{
    private $uuid;
    private $templateStamp;
    private $templateFingerprint;
    private $logger;
    private $stamp;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->uuid = $_ENV['STAMP_UUID'];
        $this->templateStamp = $_ENV['WECOM_TEMPLATE_STAMP'];
        $this->templateFingerprint = $_ENV['WECOM_TEMPLATE_FINGERPRINT'];
        $this->stamp = new Qstamp($this->uuid, $this->getStampTokenFromCache($this->uuid));
    }

    #[Route('/', name: 'app_api')]
    public function index(): Response
    {
        return new Response('<body></body>');
    }

    #[Route('/wecom/callback', name: 'app_wecom_callback')]
    public function wecom(Request $request): Response
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
        $approval_token = $_ENV['WECOM_CALLBACK_TOKEN'];
        $encodingAesKey = $_ENV['WECOM_CALLBACK_ENCODINGAESKEY'];
        $corpId = $_ENV['WECOM_CORPID'];

        $wxcpt = new WXBizMsgCrypt($approval_token, $encodingAesKey, $corpId);
        $errCode = $wxcpt->VerifyURL($msg_signature, $timestamp, $nonce, $str, $str1);
        if ($errCode == 0) {
            if ($postData) {
                $pc = new Prpcrypt($encodingAesKey);
                $arr = $pc->decrypt($str, $corpId);
                $data = simplexml_load_string($arr[1], 'SimpleXMLElement', LIBXML_NOCDATA);
                // dump($data);

                $contacts = new Contacts($this->getWecomTokenFromCache('CONTACTS'));
                $approval = new Approval($this->getWecomTokenFromCache('APPROVAL'));

                if ($data->Event == 'sys_approval_change' && (string)$data->ApprovalInfo->StatuChangeEvent === "2") {
                    $applicant = (string)$data->ApprovalInfo->Applyer->UserId;
                    $spNo = (string)$data->ApprovalInfo->SpNo;
                    switch ((string)$data->ApprovalInfo->TemplateId) {
                        case "$this->templateStamp":
                            // $this->logger->warning("use stamp");
                            $this->stamp->pushApplication($this->stamp->applicationIdFromWecom($spNo), $this->stamp->getUid($applicant), $approval->getFieldValue($spNo, '用章次数'));
                            break;
                        case "$this->templateFingerprint":
                            // $this->logger->warning("add fingerprint");
                            $this->stamp->addFingerprint($this->stamp->getUid(), $applicant);
                            break;
                    }
                }

                if ($data->Event == 'change_contact' && $data->ChangeType == 'update_tag' && $data->DelUserItems) {
                    foreach (explode(',', $data->DelUserItems) as $username) {
                        $this->stamp->delFingerprint($this->stamp->getUid($username));
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
        // dump($data);
        switch ($data->cmd) {
            case 1000:  // startup
                $this->stamp->setSleepTime();
                break;
            case 1010:  // fingerprint added
                $uid = $data->data->userId;
                if ($data->data->status) {
                    // tag: "用章", tid: 1
                    $contacts = new Contacts($this->getWecomTokenFromCache('CONTACTS'));
                    $contacts->addUsersToTag(1, [$this->stamp->getUsername($uid)]);
                } else {
                    // $this->stamp->addFingerprint($uid, $username);   // where to get $username? cache?
                }
                break;
            case 1130:  // img uploaded
                $path = $_ENV['IMG_DIR_PREFIX'] . preg_replace('/\/group\d+/', '', $data->data->path);
                $media = new Media($this->getWecomTokenFromCache('APPROVAL'));
                $mediaId = $media->upload($path, 'image')->media_id;
                $approval = new Approval($this->getWecomTokenFromCache('APPROVAL'));
                $spNo = $this->stamp->applicationIdToWecom($data->data->applicationId);
                $applicant = $approval->getApplicant($spNo);
                $approver = $approval->getApprovers($spNo);
                $msg = new Message($this->getWecomTokenFromCache('APPROVAL'));
                // $data = $msg->sendTextTo("$applicant|$approver", "test", '3010040');
                $data = $msg->sendImgTo("$applicant|$approver", $mediaId, '3010040');
                break;
        }
        return $this->json(["code" => 0, "msg" => '', "data" => ""]);
    }

    /**
     * @param string $app 'CONTACTS | APPROVAL'
     *
     * @return string
     *
     */
    public function getWecomTokenFromCache($app, $refresh = false)
    {
        $cache = new FilesystemAdapter();

        if ($refresh) {
            $cache->clear("WECOM_${app}_TOKEN");
        }

        $token = $cache->get("WECOM_${app}_TOKEN", function (ItemInterface $item) use ($app) {
            $item->expiresAfter(7200);

            $fwc = new Fwc();
            $corpId = $_ENV['WECOM_CORPID'];
            $secret = $_ENV["WECOM_${app}_SECRET"];
            return $fwc->getAccessToken($corpId, $secret);
        });

        return $token;
    }

    public function getStampTokenFromCache($uuid, $refresh = false)
    {
        $cache = new FilesystemAdapter();

        if ($refresh) {
            $cache->clear("STAMP_TOKEN");
        }

        $token = $cache->get("STAMP_TOKEN", function (ItemInterface $item) use ($uuid) {
            $item->expiresAfter(7200);

            $stamp = new Qstamp($uuid);
            $key = $_ENV["STAMP_APP_KEY"];
            $secret = $_ENV["STAMP_APP_SECRET"];
            return $stamp->getToken($key, $secret);
        });

        return $token;
    }

    #[Route('/test')]
    public function test(){
        $data = $this->stamp->listFingerprints()->getContent();
        // $data = $this->stamp->delFingerprint(1);
        // $data = $this->getStampTokenFromCache($this->uuid);
        dump($data);
        return new Response('<body></body>');
    }
}
