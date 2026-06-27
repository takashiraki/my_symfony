<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\MoneyDiary;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class MoneyDiaryVoter extends Voter
{
    public const VIEW = 'MONEY_DIARY_VIEW';
    public const EDIT = 'MONEY_DIARY_EDIT';
    public const DELETE = 'MONEY_DIARY_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof MoneyDiary;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $account = $token->getUser();

        if (!$account instanceof Account) {
            return false;
        }

        /** @var MoneyDiary $subject */
        $owner = $subject->getUser();

        return $owner !== null && $owner === $account->getUser();
    }
}