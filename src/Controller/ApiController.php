<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\wecom\callback\Prpcrypt;
use App\wecom\callback\WXBizMsgCrypt;
use Alzee\Qstamp\Qstamp;
use Alzee\Fwc\Fwc;

#[Route('/api')]
class ApiController extends AbstractController
{
    private $uuid = '0X3600303238511239343734';
    private $T_STAMP= 'C4NyFxsNsBuQ5PdsCbaGzYeUQ6u6bT4Teg6BUE1it';
    private $T_FINGERPRINT = '3WK7zYJYf5SyLeiEqedzYYWbwddQMeEi3nwbTujq';
    private $stamp_token;
    private $logger;
    private $stamp;

    public function __construct(LoggerInterface $logger)
    {
        $this->stamp_token = $_ENV['stamp_token'];
        $this->logger = $logger;
        $this->stamp = new Qstamp($this->uuid, $this->stamp_token);
    }

    #[Route('/', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
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
        $approval_token = $_ENV['approval_token'];
        $encodingAesKey = $_ENV['approval_EncodingAESKey'];
        $corpId = $_ENV['wecom_corpid'];

        $wxcpt = new WXBizMsgCrypt($approval_token, $encodingAesKey, $corpId);
        $errCode = $wxcpt->VerifyURL($msg_signature, $timestamp, $nonce, $str, $str1);
        if ($errCode == 0) {
            if ($postData) {
                $pc = new Prpcrypt($_ENV['approval_EncodingAESKey']);
                $arr = $pc->decrypt($str, $_ENV['wecom_corpid']);
                $data = simplexml_load_string($arr[1], 'SimpleXMLElement', LIBXML_NOCDATA);
                // dump($data);

                if ($data->Event == 'sys_approval_change' && (string)$data->ApprovalInfo->StatuChangeEvent === "2") {
                    $applicant = (string)$data->ApprovalInfo->Applyer->UserId;
                    $applicationId = 1 . substr((string)$data->ApprovalInfo->SpNo, 4);
                    switch ((string)$data->ApprovalInfo->TemplateId) {
                        case "$this->T_STAMP":
                            $this->logger->warning("use stamp");
                            $this->stamp->pushApplication($applicationId, $this->stamp->getUid($applicant));
                            break;
                        case "$this->T_FINGERPRINT":
                            $this->logger->warning("add fingerprint");
                            $this->stamp->addFingerprint($this->stamp->getUid(), $applicant);
                            break;
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
        $msg = match ($data->cmd) {
            1000 => $this->stamp->setSleepTime($data->data->sleepTime),
            1130 => $this->stamp->uploadPic(),
            default => true,
        };
        $resp = new Response();
        return $resp;
    }

    #[Route('/sleep')]
    public function sleepTime(){
        $this->stamp->setSleepTime();
        return new Response('<body></body>');
    }

    #[Route('/token')]
    public function getToken(){
        $token = $this->stamp->getToken($_ENV['stamp_app_key'], $_ENV['stamp_app_secret']);
        return new Response('<body></body>');
    }

    #[Route('/test')]
    public function test(){
        $fwc = new Fwc();
        // $token = $fwc->getAccessToken($_ENV['wecom_corpid'], $_ENV['WECOM_CONTACTS_SECRET']);
        // dump($token);
        return new Response('<body></body>');
    }
}
