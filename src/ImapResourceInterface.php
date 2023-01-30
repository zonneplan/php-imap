<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use IMAP\Connection;
use Javanile\Imap2\Connection as OAuthConnection;


interface ImapResourceInterface
{
    /**
     * Get IMAP resource stream.
     */
    public function getStream(): Connection|OAuthConnection;

    /**
     * Clear last mailbox used cache.
     */
    public function clearLastMailboxUsedCache(): void;
}
