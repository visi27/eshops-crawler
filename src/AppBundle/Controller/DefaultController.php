<?php

namespace AppBundle\Controller;


use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage2")
     */
    public function indexAction()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            http_response_code(405);
            die;
        }

        try {
            $message = Message::fromRawPostData();
            $validator = new MessageValidator();
            $validator->validate($message);

            if (in_array($message['Type'], ['SubscriptionConfirmation', 'UnsubscribeConfirmation'])) {
                file_get_contents($message['SubscribeURL']);
            }

            $this->get("logger")->notice($message['Message'] . "n");

        } catch (\Exception $e) {
            http_response_code(404);
            die;
        }
    }

    /**
     * @Route("/publish", name="publish-aws-message")
     */
    public function publishAction(){
        $message = ['test' => 'Keep Messaging'];

//        // Optional config to override default options
//        $options = [
//            'push_notifications' => 0,
//            'message_delay'      => 0
//        ];

        //$this->get('uecode_qpush.eshops_pages')->publish($message, $options);
        $message = ['test' => '1'];
        $this->get('uecode_qpush.eshops_pages')->publish($message);
        $message = ['test' => '2'];
        $this->get('uecode_qpush.eshops_pages')->publish($message);
        $message = ['test' => '3'];
        $this->get('uecode_qpush.eshops_pages')->publish($message);

        return new Response("<html><body>"."Messages Sent"."</body></html>");
    }

    /**
     * @Route("/recive", name="recive-aws-message")
     */
    public function reciveAction(){

        $options = [
            'messages_to_receive' => 3
        ];

        $messages = $this->get('uecode_qpush.eshops_pages')->receive($options);

        $response = "";
        foreach ($messages as $message) {
            $response .= $message->getBody()["test"]."<br/>";
        }
        return new Response("<html><body>".$response."</body></html>");
    }
}
