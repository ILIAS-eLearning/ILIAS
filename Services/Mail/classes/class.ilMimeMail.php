<?php declare(strict_types=1);

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

use ILIAS\Refinery\Factory as Refinery;

class ilMimeMail
{
    public const MAIL_SUBJECT_PREFIX = '[ILIAS]';
    protected static ?ilMailMimeTransport $defaultTransport = null;
    protected ilMailMimeSender $sender;
    protected ilMailMimeSubjectBuilder $subjectBuilder;
    protected ilSetting $settings;
    protected string $subject = '';
    protected string $body = '';
    protected string $finalBody = '';
    protected string $finalBodyAlt = '';
    /** @var string[] */
    protected array $sendto = [];
    /** @var string[] */
    protected array $acc = [];
    /** @var string[] */
    protected array $abcc = [];
    /** @var array<string, array{path: string, cid: string, name: string}> */
    protected array $images = [];
    /** @var string[] */
    protected array $aattach = [];
    /** @var string[] */
    protected array $actype = [];
    /** @var string[] */
    protected array $adispo = [];
    /** @var string[] */
    protected array $adisplay = [];
    private Refinery $refinery;

    public function __construct()
    {
        global $DIC;
        $this->settings = $DIC->settings();

        if (!(self::getDefaultTransport() instanceof ilMailMimeTransport)) {
            $factory = $DIC["mail.mime.transport.factory"];
            self::setDefaultTransport($factory->getTransport());
        }

        $this->subjectBuilder = new ilMailMimeSubjectBuilder($this->settings, self::MAIL_SUBJECT_PREFIX);
        $this->refinery = $DIC->refinery();
    }

    public static function setDefaultTransport(?ilMailMimeTransport $transport) : void
    {
        self::$defaultTransport = $transport;
    }

    public static function getDefaultTransport() : ?ilMailMimeTransport
    {
        return self::$defaultTransport;
    }

    public function Subject(string $subject, bool $addPrefix = false, string $contextPrefix = '') : void
    {
        $this->subject = $this->subjectBuilder->subject($subject, $addPrefix, $contextPrefix);
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function From(ilMailMimeSender $sender) : void
    {
        $this->sender = $sender;
    }

    /**
     * @param string|string[] $to To email address, accept both a single address or an array of addresses
     */
    public function To($to) : void
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
    public function Cc($cc) : void
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
    public function Bcc($bcc) : void
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
    public function getTo() : array
    {
        return $this->sendto;
    }

    /**
     * @return string[]
     */
    public function getCc() : array
    {
        return $this->acc;
    }

    /**
     * @return string[]
     */
    public function getBcc() : array
    {
        return $this->abcc;
    }

    public function Body(string $body) : void
    {
        $this->body = $body;
    }

    public function getFinalBody() : string
    {
        return $this->finalBody;
    }

    public function getFinalBodyAlt() : string
    {
        return $this->finalBodyAlt;
    }

    public function getFrom() : ilMailMimeSender
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
    ) : void {
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
    public function getAttachments() : array
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

    /**
     * @return array{path: string, cid: string, name: string}[] An array of images. Each element must container
     * to associative keys, 'path', 'cid' and 'name'
     */
    public function getImages() : array
    {
        return array_values($this->images);
    }

    protected function build() : void
    {
        global $DIC;

        $this->finalBodyAlt = '';
        $this->finalBody = '';
        $this->images = [];

        if ($DIC->settings()->get('mail_send_html', '0')) {
            $skin = $DIC['ilClientIniFile']->readVariable('layout', 'skin');

            $this->buildBodyMultiParts($skin);
            $this->buildHtmlInlineImages($skin);
        } else {
            $this->finalBody = $this->removeHTMLTags($this->body);
        }
    }

    private function removeHTMLTags(string $maybeHTML) : string
    {
        $maybeHTML = str_ireplace(['<br />', '<br>', '<br/>'], "\n", $maybeHTML);
        
        return strip_tags($maybeHTML);
    }

    protected function buildBodyMultiParts(string $skin) : void
    {
        if ($this->body === '') {
            $this->body = ' ';
        }

        if (strip_tags($this->body, '<b><u><i><a>') === $this->body) {
            // Let's assume(!) that there is no HTML
            // (except certain tags, e.g. used for object title formatting, where the consumer is not aware of this),
            // so convert "\n" to "<br>"
            $this->finalBodyAlt = strip_tags($this->body);
            $this->body = $this->refinery->string()->makeClickable()->transform(nl2br($this->body));
        } else {
            // if there is HTML, convert "<br>" to "\n" and strip tags for plain text alternative
            $this->finalBodyAlt = strip_tags(str_ireplace(["<br />", "<br>", "<br/>"], "\n", $this->body));
        }

        $this->finalBody = str_replace('{PLACEHOLDER}', $this->body, $this->getHtmlEnvelope($skin));
    }

    protected function getHtmlEnvelope(string $skin) : string
    {
        $bracket_path = './Services/Mail/templates/default/tpl.html_mail_template.html';

        if ($skin !== 'default') {
            $tplpath = './Customizing/global/skin/' . $skin . '/Services/Mail/tpl.html_mail_template.html';

            if (is_file($tplpath)) {
                $bracket_path = './Customizing/global/skin/' . $skin . '/Services/Mail/tpl.html_mail_template.html';
            }
        }

        return file_get_contents($bracket_path);
    }

    protected function buildHtmlInlineImages(string $skin) : void
    {
        $this->gatherImagesFromDirectory('./Services/Mail/templates/default/img');

        if ($skin !== 'default') {
            $skinDirectory = './Customizing/global/skin/' . $skin . '/Services/Mail/img';
            if (is_dir($skinDirectory) && is_readable($skinDirectory)) {
                $this->gatherImagesFromDirectory($skinDirectory, true);
            }
        }
    }

    protected function gatherImagesFromDirectory(string $directory, bool $clearPrevious = false) : void
    {
        if ($clearPrevious) {
            $this->images = [];
        }

        foreach (new RegexIterator(
            new DirectoryIterator($directory),
            '/\.(jpg|jpeg|gif|svg|png)$/i'
        ) as $file) {
            /** @var SplFileInfo $file */
            $cid = 'img/' . $file->getFilename();

            $this->images[$cid] = [
                'path' => $file->getPathname(),
                'cid' => $cid,
                'name' => $file->getFilename()
            ];
        }
    }

    public function Send(ilMailMimeTransport $transport = null) : bool
    {
        if (!($transport instanceof ilMailMimeTransport)) {
            $transport = self::getDefaultTransport();
        }

        $this->build();

        return $transport->send($this);
    }
}
