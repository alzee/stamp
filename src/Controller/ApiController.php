<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;
use App\wecom\callback\WXBizMsgCrypt;

#[Route('/api')]
class ApiController extends AbstractController
{
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
        $echostr = $query->get('echostr');
        $logger->debug("**********");
        $logger->debug($msg_signature);
        $logger->debug($timestamp);
        $logger->debug($nonce);
        $logger->debug($echostr);
        $logger->debug("**********");

        $xml = $request->getContent();

        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->deserialize($xml, Encrypt::class, 'xml');

        $token = $_ENV['approval_token'];
        $encodingAesKey = $_ENV['approval_EncodingAESKey'];
        $corpId = $_ENV['wecom_corpid'];

        $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
        $errCode = $wxcpt->VerifyURL($msg_signature, $timestamp, $nonce, $echostr, $echostr);
        if ($errCode == 0) {
            echo $echostr;
        } else {
            print("ERR: " . $errCode . "\n\n");
        }

        return new Response('');
    }
}
