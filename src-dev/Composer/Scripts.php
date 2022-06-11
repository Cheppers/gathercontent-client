<?php

namespace Cheppers\GatherContent\Composer;

use Sweetchuck\GitHooks\GitHookManager as GitHooksScripts;
use Composer\Script\Event;

class Scripts
{
    public static function postInstallCmd(Event $event)
    {
//        GitHooksScripts::deploy($event);
    }

    public static function postUpdateCmd(Event $event)
    {
//        GitHooksScripts::deploy($event);
    }
}
