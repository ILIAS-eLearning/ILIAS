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

use ILIAS\Mail\Mime\Presentation\MailBodyComposer;
use ILIAS\Mail\Mime\Presentation\MailBodySource;
use ILIAS\Mail\Mime\Presentation\Asset\InlineImages;

class ilMimeMail
{
    final public const string MAIL_SUBJECT_PREFIX = '[ILIAS]';

    protected static ?ilMailMimeTransport $default_transport = null;

    protected ilMailMimeSender $sender;
    protected ilMailMimeSubjectBuilder $subject_builder;
    protected ilSetting $settings;
    protected string $subject = '';
    protected string $body = '';
    protected string $final_body = '';
    protected string $final_body_alt = '';
    /** @var string[] */
    protected array $sendto = [];
    /** @var string[] */
    protected array $acc = [];
    /** @var string[] */
    protected array $abcc = [];
    protected InlineImages $images;
    /** @var string[] */
    protected array $aattach = [];
    /** @var string[] */
    protected array $actype = [];
    /** @var string[] */
    protected array $adispo = [];
    /** @var string[] */
    protected array $adisplay = [];
    private ?MailBodyComposer $body_composer;
    /** @var Closure(string): string|null */
    private ?Closure $to_html_transformation = null;
    private readonly \ILIAS\DI\Container $dic;

    public function __construct(?MailBodyComposer $body_composer = null)
    {
        global $DIC;

        $this->dic = $DIC;

        $this->settings = $this->dic->settings();

        if (!(self::getDefaultTransport() instanceof ilMailMimeTransport)) {
            $factory = $DIC->mail()->mime()->transportFactory();
            self::setDefaultTransport($factory->getTransport());
        }

        $this->subject_builder = new ilMailMimeSubjectBuilder($this->settings, self::MAIL_SUBJECT_PREFIX);
        $this->body_composer = $body_composer;
        $this->images = InlineImages::none();
    }

    /**
     * Resolved on demand, so that merely constructing a mail stays free of the
     * services only its delivery format requires.
     */
    private function bodyComposer(): MailBodyComposer
    {
        return $this->body_composer ??= $this->dic->mail()->mime()->bodyComposer();
    }

    public static function setDefaultTransport(?ilMailMimeTransport $transport): void
    {
        self::$default_transport = $transport;
    }

    public static function getDefaultTransport(): ?ilMailMimeTransport
    {
        return self::$default_transport;
    }

    public function Subject(string $subject, bool $add_prefix = false, string $context_prefix = ''): void
    {
        $this->subject = $this->subject_builder->subject($subject, $add_prefix, $context_prefix);
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function From(ilMailMimeSender $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @param string|string[] $to To email address, accept both a single address or an array of addresses
     */
    public function To($to): void
    {
        if (is_array($to)) {
            $this->sendto = $to;
        } else {
            $this->sendto[] = $to;
        }
    }

    /**
     * @param string|string[] $cc CC email address, accept both a single address or an array of addresses
     */
    public function Cc($cc): void
    {
        if (is_array($cc)) {
            $this->acc = $cc;
        } else {
            $this->acc[] = $cc;
        }
    }

    /**
     * @param string|string[] $bcc BCC email address, accept both a single address or an array of addresses
     */
    public function Bcc($bcc): void
    {
        if (is_array($bcc)) {
            $this->abcc = $bcc;
        } else {
            $this->abcc[] = $bcc;
        }
    }

    /**
     * @return string[]
     */
    public function getTo(): array
    {
        return $this->sendto;
    }

    /**
     * @return string[]
     */
    public function getCc(): array
    {
        return $this->acc;
    }

    /**
     * @return string[]
     */
    public function getBcc(): array
    {
        return $this->abcc;
    }

    /**
     * @param Closure(string): string|null $to_html_transformation
     */
    public function Body(string $body, ?Closure $to_html_transformation = null): void
    {
        $this->body = $body;
        $this->to_html_transformation = $to_html_transformation;
    }

    public function getFinalBody(): string
    {
        return $this->final_body;
    }

    public function getFinalBodyalt(): string
    {
        return $this->final_body_alt;
    }

    public function getFrom(): ilMailMimeSender
    {
        return $this->sender;
    }

    /**
     * @param string $filename Path of the file to attach
     * @param string $file_type MIME-type of the file. default to 'application/x-unknown-content-type'
     * @param string $disposition Instruct the Mailclient to display the file if possible ("inline")
     *                            or always as a link ("attachment") possible values are "inline", "attachment"
     * @param string|null $display_name Filename to use in email (if different from source file)
     */
    public function Attach(
        string $filename,
        string $file_type = '',
        string $disposition = 'inline',
        ?string $display_name = null
    ): void {
        if ($file_type === '') {
            $file_type = 'application/octet-stream';
        }

        $this->aattach[] = $filename;
        $this->actype[] = $file_type;
        $this->adispo[] = $disposition;
        $this->adisplay[] = $display_name;
    }

    /**
     * @return array{path: string, name: string}[]
     */
    public function getAttachments(): array
    {
        $attachments = [];

        $i = 0;
        foreach ($this->aattach as $attachment) {
            $name = '';
            if (isset($this->adisplay[$i]) && is_string($this->adisplay[$i]) && $this->adisplay[$i] !== '') {
                $name = $this->adisplay[$i];
            }

            $attachments[] = [
                'path' => $attachment,
                'name' => $name
            ];
            ++$i;
        }

        return $attachments;
    }

    public function getImages(): InlineImages
    {
        return $this->images;
    }

    protected function build(): void
    {
        $composed = $this->bodyComposer()->compose(
            new MailBodySource($this->body, $this->to_html_transformation)
        );

        $this->final_body = $composed->body();
        $this->final_body_alt = $composed->alternativeBody() ?? '';
        $this->images = $composed->images();
    }

    public function Send(?ilMailMimeTransport $transport = null): bool
    {
        if (!($transport instanceof ilMailMimeTransport)) {
            $transport = self::getDefaultTransport();
        }

        $this->build();

        return $transport->send($this);
    }
}
