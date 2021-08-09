<?php

namespace Knawat\Dropshipping\Block\Adminhtml;

use Knawat\Dropshipping\Helper\ManageConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Import
 * @package Knawat\Dropshipping\Block\Adminhtml
 */
class Import extends \Magento\Backend\Block\Template
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $configModel;

    /**
     * @var ManageConfig
     */
    protected $configHelper;

    /**
     *knawat default configuration path value
     */
    const PATH_KNAWAT_DEFAULT = 'knawat/store/';

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Settings constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $configModel
     * @param ManageConfig $configHelper
     * @param ProductMetadataInterface $productMetadata
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Config $configModel,
        ManageConfig $configHelper,
        ProductMetadataInterface $productMetadata,
        SerializerInterface $serializer
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->configModel = $configModel;
        $this->configHelper = $configHelper;
        $this->productMetadata = $productMetadata;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigData($path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::PATH_KNAWAT_DEFAULT . $path, $storeScope);
    }

    /**
     * @return bool|mixed
     * @throws LocalizedException
     */
    public function getImportStatus()
    {
        $configConnection = $this->configModel->getConnection();
        $identifier = 'kdropship_import';
        $select = $configConnection->select()->from($this->configModel->getMainTable())->where('path=?', self::PATH_KNAWAT_DEFAULT . $identifier);
        $configData = $configConnection->fetchRow($select);
        if (!empty($configData) && isset($configData['value'])) {
            $importData = $configData['value'];
        }

        if (!empty($importData)) {
            return $this->serializer->unserialize($importData);
        }
        return false;
    }

    /**
     * check Knawat connection status
     * @return bool
     */
    public function isKnawatConnected(): bool
    {
        return !!$this->configHelper->getToken();
    }

    public function isVersionTwo(): bool
    {
        $version = $this->productMetadata->getVersion();
        $versionCompare = version_compare($version, "2.2");
        return $versionCompare == -1;
    }
}
