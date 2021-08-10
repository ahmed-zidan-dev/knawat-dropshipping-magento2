<?php

namespace Knawat\Dropshipping\Controller\Adminhtml\Dropshipping;

use Magento\Backend\App\Action\Context;
use \Knawat\Dropshipping\Helper\General;
use \Magento\Framework\App\ResponseInterface;
use \Magento\Framework\Controller\ResultInterface;

/**
 * Class Productsyncbar
 * @package Knawat\Dropshipping\Controller\Adminhtml\Dropshipping
 */
class Productsyncbar extends \Magento\Backend\App\Action
{
    /**
     * @var General
     */
    protected $generalHelper;

    const PATH_KNAWAT_DEFAULT = 'knawat/store/';

    /**
     * Productsyncbar constructor.
     * @param Context $context
     * @param General $generalHelper
     */
    public function __construct(
        Context $context,
        General $generalHelper
    )
    {
        $this->generalHelper = $generalHelper;
        parent::__construct($context);
    }


    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $importStartTime = self::PATH_KNAWAT_DEFAULT . 'kdropship_import_start_time';
        $lastImportCount = self::PATH_KNAWAT_DEFAULT . 'knawat_last_imported_count';
        $lastImported = self::PATH_KNAWAT_DEFAULT . 'knawat_last_imported';
        $processedAt = self::PATH_KNAWAT_DEFAULT . 'knawat_last_imported_process_time';
        $importProcessLock = self::PATH_KNAWAT_DEFAULT . 'kdropship_import_process_lock';
        $configArray = [
            $importStartTime,
            $lastImportCount,
            $lastImported,
            $processedAt,
            $importProcessLock
        ];

        foreach ($configArray as $configValue) {
            $this->generalHelper->setConfig($configValue, null);
        }
        $this->_redirect($this->_redirect->getRefererUrl());
    }


    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Knawat_Dropshipping::productsyncbar');
    }
}
