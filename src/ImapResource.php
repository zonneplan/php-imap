<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use Ddeboer\Imap\Exception\ReopenMailboxException;
use IMAP\Connection;
use Javanile\Imap2\Connection as OAuthConnection;

/**
 * An imap resource stream.
 */
final class ImapResource implements ImapResourceInterface
{
    private Connection|OAuthConnection $resource;
    private ?MailboxInterface $mailbox           = null;
    private static ?string $lastMailboxUsedCache = null;

    /**
     * Constructor.
     */
    public function __construct(Connection|OAuthConnection $resource, MailboxInterface $mailbox = null)
    {
        $this->resource = $resource;
        $this->mailbox  = $mailbox;
    }

    public function getStream(): Connection|OAuthConnection
    {
        $this->initMailbox();

        return $this->resource;
    }

    public function clearLastMailboxUsedCache(): void
    {
        self::$lastMailboxUsedCache = null;
    }

    /**
     * If connection is not currently in this mailbox, switch it to this mailbox.
     */
    private function initMailbox(): void
    {
        if (null === $this->mailbox || self::isMailboxOpen($this->mailbox, $this->resource)) {
            return;
        }

        \set_error_handler(static function (): bool {
            return true;
        });

        \imap2_reopen($this->resource, $this->mailbox->getFullEncodedName());

        \restore_error_handler();

        if (self::isMailboxOpen($this->mailbox, $this->resource)) {
            return;
        }

        throw new ReopenMailboxException(\sprintf('Cannot reopen mailbox "%s"', $this->mailbox->getName()));
    }

    /**
     * Check whether the current mailbox is open.
     */
    private static function isMailboxOpen(MailboxInterface $mailbox, Connection|OAuthConnection $resource): bool
    {
        $currentMailboxName = $mailbox->getFullEncodedName();
        if ($currentMailboxName === self::$lastMailboxUsedCache) {
            return true;
        }

        self::$lastMailboxUsedCache = null;
        $check                      = \imap2_check($resource);
        $return                     = false !== $check && $check->Mailbox === $currentMailboxName;

        if (true === $return) {
            self::$lastMailboxUsedCache = $currentMailboxName;
        }

        return $return;
    }
}
