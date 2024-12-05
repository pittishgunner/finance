<?php

namespace App\Service;

use App\Repository\AccountRepository;
use App\Repository\CategoryRuleRepository;
use App\Repository\RecordRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpKernel\KernelInterface;
use WebPush\Message;
use WebPush\Subscription as WebPushSubscription;
use WebPush\WebPush;

class NotificationsService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly WebPush       $webPushService,
    )
    {
    }

    public function notifySubscribedUsers(string $body)
    {
        $Subscriptions = $this->subscriptionRepository->findAll();
        if (!empty($Subscriptions)) {
            foreach ($Subscriptions as $Subscription) {
                $notificationAndSubscription = $this->getNotificationByType($Subscription->getSubscription(), $body);
                $statusReport = $this->webPushService->send(
                    $notificationAndSubscription['notification'],
                    $notificationAndSubscription['subscription'],
                );
            }
        }
    }


    public function getNotificationByType(string $content, string $body = 'This is a test!'): array
    {
        $message = Message::create('MF', $body)
            ->ltr()
            //->renotify()
            ->vibrate(200, 300, 200, 300)
            //->withImage('https://placebear.com/1024/512')
           // ->withIcon('https://placebear.com/512/512')
            ///->withBadge('https://placebear.com/256/256')
            //->withData(['foo' => 'BAR'])
            //->withTag('tag1')
            //->withLang('fr_FR')
            //->mute()
            ->withTimestamp(time())
            //->addAction(WebPushAction::create('accept', 'Accept'))
           //->addAction(WebPushAction::create('cancel', 'Cancel'))
        ;
        $notification = \WebPush\Notification::create()
            //->highUrgency()
            ->withPayload($message->toString());
        $subscription = WebPushSubscription::createFromString($content);

        return [
            'notification' => $notification,
            'subscription' => $subscription
        ];
    }
}
