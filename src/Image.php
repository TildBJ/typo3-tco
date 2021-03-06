<?php
declare(strict_types=1);

namespace TildBJ\Tco;

/**
 * Class Image
 *
 * @package TildBJ\Tco
 */
final class Image
{
    use Common\CanBeExcluded;

    /**
     * @var string $label
     */
    private $label;

    /**
     * @var string $fieldName
     */
    private $fieldName;

    /**
     * @var int $maxitems
     */
    private $maxitems = null;

    /**
     * @var int $minitems
     */
    private $minitems = null;

    /**
     * @var array $cropVariants
     */
    private $cropVariants = [];

    /**
     * @var string $showItems
     */
    private $showItems = 'title,alternative';

    /**
     * @var string $filePalette
     */
    private $filePalette = '--palette--;;filePalette';

    /**
     * @var bool $disableDefaultCropVariant
     */
    private $disableDefaultCropVariant = false;

    /**
     * @param string $label
     * @param string $fieldName
     */
    public function __construct(string $label, string $fieldName)
    {
        $this->label = $label;
        $this->fieldName = $fieldName;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->cropVariants)) {
            $this->addDefaultCropVariant();
        }
        foreach ($this->cropVariants as $key => $cropVariant) {
            $cropVariants[$key] = $cropVariant;
        }
        $cropVariants['default']['disabled'] = $this->disableDefaultCropVariant;
        $tca = [
            'exclude' => $this->exclude,
            'label' => $this->label,
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                $this->fieldName,
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'minitems' => $this->minitems,
                    'maxitems' => $this->maxitems,
                    'overrideChildTca' => [
                        'columns' => [
                            'crop' => [
                                'config' => [
                                    'cropVariants' => $cropVariants,
                                ],
                            ],
                        ],
                        'types' => [
                            2 => [
                                'showitem' => $this->showItems . ',' . $this->filePalette,
                            ],
                        ],
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ];

        return $tca;
    }

    /**
     * @param int $max
     * @return Image
     */
    public function setMaxItems(int $max): self
    {
        $this->maxitems = $max;

        return $this;
    }

    /**
     * @param int $min
     * @return Image
     */
    public function setMinItems(int $min): self
    {
        $this->minitems = $min;

        return $this;
    }

    /**
     * @return Image
     */
    public function enableCropping(): self
    {
        $items = explode(',', $this->showItems);
        if (!in_array('--linebreak--,crop', $items)) {
            $items[] = '--linebreak--,crop';
        }

        $this->showItems = implode(',', $items);

        return $this;
    }

    /**
     * @return Image
     */
    public function enableLink(): self
    {
        $items = explode(',', $this->showItems);
        if (!in_array('link', $items)) {
            $items[] = 'link';
        }

        $this->showItems = implode(',', $items);

        return $this;
    }

    /**
     * @param string $key
     * @param string $label
     * @param int $x
     * @param int $y
     * @return Image
     */
    public function addCropVariant(string $key, string $label, int $x, int $y): self
    {
        if ($key !== 'default') {
            $this->disableDefaultCropVariant();
        }

        $this->cropVariants[$key] = [
            'title' => $label,
            'allowedAspectRatios' => [
                $x . ':' . $y => [
                    'title' => $x . ' x ' . $y,
                    'value' => $x / $y
                ]
            ],
        ];

        return $this;
    }

    /**
     * @return Image
     * @deprecated Will be private in future releases
     */
    public function disableDefaultCropVariant(): self
    {
        $this->disableDefaultCropVariant = true;

        return $this;
    }

    /**
     * @return void
     */
    private function addDefaultCropVariant()
    {
        $this->addCropVariant('default', 'Default', 0, 1);
        $this->disableDefaultCropVariant = false;
    }
}
