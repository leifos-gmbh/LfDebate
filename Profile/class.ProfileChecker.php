<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Leifos\Debate\Profile;

use Leifos\Debate\DomainFactory;

class ProfileChecker
{
    /**
     * @var DomainFactory
     */
    protected $domain;

    public function __construct(DomainFactory $domain)
    {
        $this->domain = $domain;
    }

    public function hasPublicProfile() : bool
    {
        $db = $this->domain->database();
        $user_id = $this->domain->user()->getId();

        $in = $db->in('usr_pref.keyword', array('public_upload', 'public_profile'), false, 'text');
        $res = $db->queryF(
            "
			SELECT usr_pref.*, ud.login, ud.firstname, ud.lastname
			FROM usr_data ud LEFT JOIN usr_pref ON usr_pref.usr_id = ud.usr_id AND $in
			WHERE ud.usr_id = %s",
            array('integer'),
            array($user_id)
        );

        $has_public_upload = false;
        $has_public_profile = false;

        while ($row = $db->fetchAssoc($res)) { // MUST be loop
            switch ($row['keyword']) {
                case 'public_upload':
                    $has_public_upload = $row['value'] === 'y';
                    break;
                case 'public_profile':
                    $has_public_profile = ($row['value'] === 'y' || $row['value'] === 'g');
                    break;
            }
        }

        // Uploaded file
        $webspace_dir = '';
        if (defined('ILIAS_MODULE')) {
            $webspace_dir = ('.' . $webspace_dir);
        }
        $webspace_dir .= ('./' . ltrim(\ilUtil::getWebspaceDir(), "./"));

        $image_dir = $webspace_dir . '/usr_images';
        $uploaded_file = $image_dir . '/usr_' . $user_id . '.jpg';

        return ($has_public_upload && $has_public_profile && is_file($uploaded_file));
    }

    public function setTimestamp() : void
    {
        \ilObjUser::_writePref($this->domain->user()->getId(), "xdbt_profile_remind", date("Ymd"));
    }

    public function hasBeenReminded() : bool
    {
        $rem = \ilObjUser::_lookupPref($this->domain->user()->getId(), "xdbt_profile_remind");
        if ($rem === date("Ymd")) {
            return true;
        }
        return false;
    }
}