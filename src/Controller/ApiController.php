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

#[Route('/api')]
class ApiController extends AbstractController
{
    private $uuid = '0X3600303238511239343734';
    private $T_STAMP= 'C4NyFxsNsBuQ5PdsCbaGzYeUQ6u6bT4Teg6BUE1it';
    private $T_FINGERPRINT = '3WK7zYJYf5SyLeiEqedzYYWbwddQMeEi3nwbTujq';
    private $stamp_token = $_ENV['stamp_token'];
    private $url = $_ENV['api_url'];

    #[Route('/', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/wecom', name: 'app_wecom')]
    public function wecom(Request $request, LoggerInterface $logger): Response
    {
        $query = $request->query;
        $msg_signature= $query->get('msg_signature');
        $timestamp = $query->get('timestamp');
        $nonce = $query->get('nonce');
        //$logger->debug("**********");
        //$logger->debug($msg_signature);
        //$logger->debug($timestamp);
        //$logger->debug($nonce);
        //$logger->debug("**********");

        $postData = $request->getContent();

        if ($postData) {
            $xml = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA);
            $str = $xml->Encrypt;
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
                dump($data);

                if ($data->Event == 'sys_approval_change' && $data->ApprovalInfo->StatuChangeEvent == "2") {
                    //dump($data->Event);
                    //dump($data->ApprovalInfo->StatuChangeEvent);
                    //dump($data->ApprovalInfo->TemplateId);
                    switch ($data->ApprovalInfo->TemplateId) {
                        case "$this->T_STAMP":
                            $logger->warning("use stamp");
                            $this->pushApplication();
                            break;
                        case "$this->T_FINGERPRINT":
                            $logger->warning("add fingerprint");
                            $this->addFingerprint($);
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

    public function pushApplication($applicationId, $uid, $totalCount = 5, $needCount=5, $uuid = $this->uuid)
    {
        $api = "application/push";
        # curl -H "tToken: $token" "$api_url/$api" -d "applicationId=11111&userId=$uid&totalCount=5&needCount=5&uuid=$uuid"
    }

    public function changeMode($mode, $uuid = $this->uuid)
    {
        $api = "device/model";
        # curl -H "tToken: $token" "$api_url/$api" -d "uuid=$uuid&model=0"
    }

    public function listFingerprints($uuid = $this->uuid)
    {
        $api = "finger/list";
        # curl -H "tToken: $token" "$api_url/$api" -d "uuid=$uuid"
    }

    public function addFingerprint($uid, $username, $uuid = $this->uuid)
    {
        $api = "/finger/add";
        $username='';
    }

    public function delFingerprint($uid, $uuid = $this->uuid)
    {
        $api = "/finger/del";
    }

    public function idUse($uid, $username, $uuid = $this->uuid)
    {
        $api = "device/idUse";
        // curl -H "tToken: $token" "$api_url/$api" -d "userId=$uid&username=$uname&uuid=$uuid"
    }

    public function records($uuid = $this->uuid)
    {
        $api = "record/list";
        // curl -H "tToken: $token" "$api_url/$api" -d "uuid=$uuid"
    }
}
