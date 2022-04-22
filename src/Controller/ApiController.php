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
    private $stamp_token;
    private $url;
    private $client;
    private $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->stamp_token = $_ENV['stamp_token'];
        $this->url = $_ENV['api_url'];
        $this->client = $client;
        $this->logger = $logger;
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
                // dump($data);

                if ($data->Event == 'sys_approval_change' && (string)$data->ApprovalInfo->StatuChangeEvent === "2") {
                    switch ((string)$data->ApprovalInfo->TemplateId) {
                        case "$this->T_STAMP":
                            $this->logger->warning("use stamp");
                            $this->pushApplication((string)$data->ApprovalInfo->SpNo, );
                            break;
                        case "$this->T_FINGERPRINT":
                            $this->logger->warning("add fingerprint");
                            $this->addFingerprint(57, (string)$data->ApprovalInfo->Applyer->UserId);
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

    public function pushApplication($applicationId, $uid, $totalCount = 5, $needCount=0)
    {
        $api = "/application/push";
        $body = [
            'applicationId' => $applicationId,
            'userId' => $uid,
            'totalCount' => $totalCount,
            // 'needCount' => $needCount,
            'uuid' => $this->uuid
        ];
        $response = $this->request($api, $body);
    }

    public function changeMode($mode)
    {
        $api = "/device/model";
        # curl -H "tToken: $token" "$api_url/$api" -d "uuid=$uuid&model=0"
    }

    public function listFingerprints()
    {
        $api = "/finger/list";
        $body = [
            'uuid' => $this->uuid
        ];
        $response = $this->request($api, $body);
    }

    public function addFingerprint($uid, $username)
    {
        $api = "/finger/add";
        $body = [
            'userId' => $uid,
            'username' => $username,
            'uuid' => $this->uuid
        ];
        $response = $this->request($api, $body);
    }

    public function delFingerprint($uid)
    {
        $api = "/finger/del";
    }

    public function idUse($uid, $username)
    {
        $api = "/device/idUse";
        // curl -H "tToken: $token" "$api_url/$api" -d "userId=$uid&username=$uname&uuid=$uuid"
    }

    public function records()
    {
        $api = "/record/list";
        // curl -H "tToken: $token" "$api_url/$api" -d "uuid=$uuid"
    }

    public function getUid()
    {
    }

    public function request($api, $body)
    {
        $headers = ["tToken: $this->stamp_token"];
        $response = $this->client->request(
            'POST',
            $this->url . $api,
            [
                'headers' => $headers,
                'body' => $body
            ]
        );
        $content = $response->getContent();
        return $response;
    }
}
