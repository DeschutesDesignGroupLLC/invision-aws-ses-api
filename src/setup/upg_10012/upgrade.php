<?php

namespace IPS\awsses\setup\upg_10012;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * 1.0.10 Upgrade Code
 */
class _Upgrade
{
    /**
     * Run upgrader
     *
     * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
     */
    public function step1()
    {
        // Migrate all member_id fields to email fields
        $stmt = \IPS\Db::i()->select('*', \IPS\awsses\Bounce\Log::$databaseTable, array('member_id IS NOT NULL AND member_id<>0 AND email IS NULL'));
        foreach ($stmt as $row) {
            $member = \IPS\Member::load($row['member_id']);
            if ($email = $member->email) {
                \IPS\Db::i()->update(\IPS\awsses\Bounce\Log::$databaseTable, array('email' => $email), array('member_id=?', $row['member_id']));
            }
        }

        return true;
    }
}
