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
    private $uuid = '0X3600303238511239343734';
    private $T_STAMP= 'C4NyFxsNsBuQ5PdsCbaGzYeUQ6u6bT4Teg6BUE1it';
    private $T_FINGERPRINT = '3WK7zYJYf5SyLeiEqedzYYWbwddQMeEi3nwbTujq';
    private $logger;
    private $stamp;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->stamp = new Qstamp($this->uuid, $this->getStampTokenFromCache($this->uuid));
    }

    #[Route('/', name: 'app_api')]
    public function index(): Response
    {
        return new Response('<body></body>');
    }

    #[Route('/wecom', name: 'app_wecom')]
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
        $approval_token = $_ENV['APPROVAL_TOKEN'];
        $encodingAesKey = $_ENV['APPROVAL_ENCODINGAESKEY'];
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
                        case "$this->T_STAMP":
                            $this->logger->warning("use stamp");
                            // $spNo: 202204220055 to 104220055
                            $this->stamp->pushApplication(1 . substr($spNo, 3), $this->stamp->getUid($applicant), $approval->getFieldValue($spNo, '用印次数'));
                            break;
                        case "$this->T_FINGERPRINT":
                            $this->logger->warning("add fingerprint");
                            $this->stamp->addFingerprint($this->stamp->getUid(), $applicant);
                            // tag: "用章", tid: 1
                            $contacts->addUsersToTag(1, [$applicant]);
                            break;
                    }
                }

                if ($data->Event == 'change_contact' && $data->ChangeType == 'update_tag' && $data->DelUserItems) {
                    $contacts->delUsersFromTag($data->TagId, $data->DelUserItems);
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
        $msg = match ($data->cmd) {
            1000 => $this->stamp->setSleepTime($data->data->sleepTime),
            1130 => $this->stamp->uploadPic(),
            default => true,
        };
        $resp = new Response();
        return $resp;
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
        // $data = $this->getStampTokenFromCache($this->uuid);
        // $data = $this->stamp->records();
        $msg = new Message($this->getWecomTokenFromCache('APPROVAL'));
        $media = new Media($this->getWecomTokenFromCache('APPROVAL'));
        // $data = $msg->sendTextTo('Houfei', "it's okay", '3010040');
        $media->upload('a.png', 'image');
        dump($data);
        return new Response('<body></body>');
    }
}
