<?php
namespace Rehike\Model\Watch\Watch8;

use Rehike\Util\ExtractUtils;
use Rehike\Model\Traits\Runs;
use Rehike\Util\ImageUtils;
use Rehike\TemplateFunctions;
use Rehike\i18n;

/**
 * Implement the model used by the video's secondary info renderer.
 * 
 * @author Taniko Yamamoto <kirasicecreamm@gmail.com>
 * @author The Rehike Maintainers
 */
class MVideoSecondaryInfoRenderer
{
    public $description;
    public $defaultExpanded;
    public $dateText;
    public $metadataRowContainer;
    public $showMoreText;
    public $showLessText;

    public function __construct($dataHost)
    {
        if (!is_null($dataHost::$secondaryInfo))
        {
            $info = &$dataHost::$secondaryInfo;
            $primaryInfo = &$dataHost::$primaryInfo;

            i18n::newNamespace("watch/secondary")->registerFromFolder("i18n/watch");

            $this->description = $info->description ?? null;
            $this->defaultExpanded = $info->defaultExpanded ?? false;
            $this->dateText = isset($primaryInfo->dateText)
                ? ExtractUtils::resolveDate($primaryInfo->dateText)
                : null;
            $this->metadataRowContainer = new MMetadataRowContainer(
                $info->metadataRowContainer->metadataRowContainerRenderer->rows,
                $dataHost
            );
            $this->showMoreText = $info->showMoreText ?? null;
            $this->showLessText = $info->showLessText ?? null;
        }
    }
}

class MMetadataRowContainer
{
    use Runs;

    public $items = [];

    public function __construct(&$rows, $dataHost)
    {
        $i18n = i18n::getNamespace("watch/secondary");
        
        // Configuration
        $addLicense = true;

        if (!is_null($rows))
        {
            $this->items = $rows;

            foreach ($this->items as $index => $item)
            foreach ($item as $type => $data)
            {
                // if ($runs = @$item->metadataRowRenderer->contents[0]->runs) foreach ($runs as $run)
                // {
                //     $url = @$run->navigationEndpoint->commandMetadata->webCommandMetadata->url;
                //     if ($url && preg_match("/\/t\/creative_commons/", $url))
                //     {
                //         $addLicense = false;
                //     }
                // }

                switch ($type)
                {
                    case "metadataRowRenderer":
                        $url = "";
                        if ($url = @$data->contents[0]->runs->navigationEndpoint->commandMetadata->webCommandMetadata->url
                        &&  str_contains($url, "/t/creative_commons/"))
                            $addLicense = false;
                            break;
                    case "richMetadataRowRenderer":
                        // Very very icky, we don't want this.
                        array_splice($this->items, $index, 1);

                        $richItems = [];

                        foreach ($data->contents as $row)
                        {
                            $row = $row->richMetadataRenderer ?? null;
                            if (!is_null($row) && $row->style == "RICH_METADATA_RENDERER_STYLE_BOX_ART")
                            {
                                $richItems[] = (object) [
                                    "richMetadataRowRenderer" => (object) [
                                        "label" => (object) [
                                            "simpleText" => $i18n->metadataGame
                                        ],
                                        "title" => $row->title,
                                        "subtitle" => $row->subtitle ?? null,
                                        "callToAction" => $row->callToAction ?? null,
                                        "navigationEndpoint" => $row->endpoint,
                                        "thumbnail" => $row->thumbnail ?? null
                                    ]
                                ];
                            }
                        }
                        break;
                }
            }
        }

        /* 
         * Also there is a new music renderer thing that is completely
         * batshit insane.
         * So enjoy this mess
         */
        // Check if engagement panels exist.
        if (isset($dataHost::$response->engagementPanels))
        // Go through the panels
        foreach ($dataHost::$response->engagementPanels as $panel)
        // Check the name of the current panel
        foreach ($panel->engagementPanelSectionListRenderer->content as $name => $content)
        if ("structuredDescriptionContentRenderer" == $name)
        // Go through the children of the panel and check the name of it
        foreach ($content->items as $item)
        foreach ($item as $name => $item)
        if ("videoDescriptionMusicSectionRenderer" == $name)
        {
            // And we're finally here. Now time for even more iteration.
            $musicItems = [];

            // Find the rows and go through them
            if (isset($item->carouselLockups)) // For some reason they don't exist on some videos.
            foreach (@$item->carouselLockups as $lockup)
            {
                // Add song title from the lockup if it exists
                if (isset($lockup->carouselLockupRenderer->videoLockup))
                {
                    $title = $i18n->metadataSong;

                    $content = TemplateFunctions::getText($lockup->carouselLockupRenderer->videoLockup->compactVideoRenderer->title);
                    $href = TemplateFunctions::getUrl($lockup->carouselLockupRenderer->videoLockup->compactVideoRenderer);
                    if ("" == $href) $href = null;

                    $musicItems[] = self::createSimpleField($title, $content, $href);
                }

                foreach (@$lockup->carouselLockupRenderer->infoRows as $row)
                {
                    $data = @$row->infoRowRenderer;
                    $element = isset($data->expandedMetadata) ? "expandedMetadata" : "defaultMetadata"; 

                    // WHY IS THE FUCKING TITLE HARDCODED UPPERCASE
                    // I AM GOING TO FUCKING KILL MYSELF
                    // love taniko
                    $title = ucfirst(strtolower(TemplateFunctions::getText(@$data->title)));

                    $content = TemplateFunctions::getText(@$data->{$element});
                    $href = TemplateFunctions::getUrl(@$data->{$element}->runs[0]);
                    if ("" == $href) $href = null;

                    $musicItems[] = self::createSimpleField($title, $content, $href);
                }
            }

            $this->items += $musicItems;
        }

        // If specified, add a license field for standard.
        if ($addLicense && !$dataHost::$isLive)
            array_unshift($this->items, self::getLicenseField());

        // Add category option
        array_unshift($this->items, self::getCategoryField($dataHost));

        // Add rich items (game in gaming video)
        if (isset($richItems) && count($richItems) > 0)
        foreach ($richItems as $item)
            array_unshift($this->items, $item);
    }

    protected function createSimpleField($title, $text, $href = null)
    {
        return (object)[
            "metadataRowRenderer" => (object)[
                "title" => (object)[
                    "runs" => [
                        self::createRun($title)
                    ]
                ],
                "contents" => [
                    (object)[
                        "runs" => [
                            self::createRun($text, $href)
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function getCategoryField($dataHost)
    {
        $i18n = i18n::getNamespace("watch/secondary");
        $title = $i18n->metadataCategory;

        $category = @$dataHost::$yt->playerResponse->microformat
            ->playerMicroformatRenderer->category
        ;

        if ($category)
        {
            return self::createSimpleField($title, $category);
        }
    }

    protected function getLicenseField()
    {
        $i18n = i18n::getNamespace("watch/secondary");

        $title = $i18n->metadataLicense;
        $text = $i18n->metadataLicenseStandard;

        return self::createSimpleField($title, $text);
    }
}
