<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\PaymentSource;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PaymentSourceVoter extends Voter
{
    public const VIEW = 'PAYMENT_SOURCE_VIEW';

    public const EDIT = 'PAYMENT_SOURCE_EDIT';

    public const DELETE = 'PAYMENT_SOURCE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof PaymentSource;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $account = $token->getUser();

        if (! $account instanceof Account) {
            return false;
        }

        /** @var PaymentSource $subject */
        $owner = $subject->getUser();

        return $owner !== null && $owner === $account->getUser();
    }
}
