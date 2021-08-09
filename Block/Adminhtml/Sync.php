<?php

namespace Knawat\Dropshipping\Block\Adminhtml;

use Knawat\Dropshipping\Helper\CommonHelper;
use Knawat\Dropshipping\Helper\General;
use Knawat\Dropshipping\Helper\ManageConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Sync
 * @package Knawat\Dropshipping\Block\Adminhtml
 */
class Sync extends \Magento\Backend\Block\Template
{

    /**
     * @var General
     */
    protected $generalHelper;

    /**
     * @var ManageConfig
     */
    protected $configHelper;

    /**
     * @var CommonHelper
     */
    protected $commonHelper;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    const PATH_KNAWAT_DEFAULT = 'knawat/store/';


    /**
     * Sync constructor.
     * @param Context $context
     * @param General $generalHelper
     * @param ManageConfig $configHelper
     * @param CommonHelper $commonHelper
     * @param TimezoneInterface $date
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        General $generalHelper,
        ManageConfig $configHelper,
        CommonHelper $commonHelper,
        TimezoneInterface $date,
        UrlInterface $urlBuilder
    )
    {
        $this->generalHelper = $generalHelper;
        $this->configHelper = $configHelper;
        $this->commonHelper = $commonHelper;
        $this->date = $date;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getImportCount(): int
    {
        return (int)$this->generalHelper->getConfigDirect('knawat_last_imported_count', true);
    }

    /**
     * @return string | bool
     */
    public function getLastImportTime()
    {
        $lastImportTime = $this->generalHelper->getConfigDirect('knawat_last_imported', true);
        if (!empty($lastImportTime)) {
            return $lastImportTime;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isKnawatConnected(): bool
    {
        return !!$this->configHelper->getToken();
    }

    /**
     * Getting total products by passing the path
     * @param $path string
     * @return bool|int
     */
    public function getTotalProductsByPath($path)
    {
        $mp = $this->commonHelper->createMP();

        if ($mp && $mp->getAccessToken() && ($total = $mp->client->get($path)->total) >= 0) {
            return $total;
        }
        return false;
    }

    /**
     * @return bool | int
     */
    public function getTotalInStockProducts()
    {
        return $this->getTotalProductsByPath('/catalog/products/count?hideOutOfStock=1');
    }

    /**
     * @return bool | int
     */
    public function getTotalProducts()
    {
        return $this->getTotalProductsByPath('/catalog/products/count');
    }

    /**
     * @return bool
     */
    public function getLastSyncProducts()
    {

        $importedAt = $this->getLastImportTime();
        if ($importedAt) {
            $path = "/catalog/products/count?lastUpdate={$importedAt}";
            $pathTotalProducts = $this->getTotalProductsByPath($path);
            $totalProducts = $this->getTotalProducts();
            if ($pathTotalProducts !== false && $totalProducts !== false) {
                return $totalProducts - $pathTotalProducts;
            }
        }
        return false;
    }

    /**
     * @return bool|\Magento\Framework\Phrase
     * @throws LocalizedException
     */
    public function getTimeMinutes()
    {
        $lastImportProcessTime = $this->generalHelper->getConfigDirect('knawat_last_imported_process_time', true);
        $date1 = $lastImportProcessTime;
        $date2 = $this->date->date()->format('Y-m-d H:i:s');
        $diff = abs(strtotime($date2) - strtotime($date1));
        if ($diff) {
            $years = floor($diff / (365 * 60 * 60 * 24));
            $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
            $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));

            $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);

            $fullDays = floor($diff / (60 * 60 * 24));
            $importCount = $this->getImportCount();
            if ($fullDays) {
                return __("We just updated <b>{$importCount} products {$fullDays} day(s) ago.</b>");
            } elseif ($hours) {
                return __("We just updated <b>{$importCount} products {$hours} hour(s) ago.</b>");
            } elseif ($minutes) {
                return __("We just updated <b>{$importCount} products {$minutes} minut(s) ago.</b>");
            }
        }
        return false;
    }

    /**
     * @return bool|float
     */
    public function getSyncBarAmount()
    {
        $totalProducts = $this->getTotalProducts();
        $syncProducts = $this->getLastSyncProducts();
        if ($totalProducts && $syncProducts) {
            return round(($syncProducts * 100) / $totalProducts);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getSyncAllUrl(): string
    {
        return $this->urlBuilder->getUrl('dropshipping/dropshipping/productsyncbar/');
    }
}
