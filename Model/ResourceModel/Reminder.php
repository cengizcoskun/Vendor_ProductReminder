<?php
/**
 * Product Reminder Resource Model
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Reminder extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('product_reminder', 'id');
    }
} 