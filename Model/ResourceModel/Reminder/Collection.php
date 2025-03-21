<?php
/**
 * Product Reminder Collection
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Model\ResourceModel\Reminder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\ProductReminder\Model\Reminder;
use Vendor\ProductReminder\Model\ResourceModel\Reminder as ReminderResource;

class Collection extends AbstractCollection
{
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Reminder::class, ReminderResource::class);
    }
    
    /**
     * Filter collection by customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }
    
    /**
     * Filter collection by pending status
     *
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter('status', Reminder::STATUS_PENDING);
        return $this;
    }
    
    /**
     * Filter collection by reminder date
     *
     * @param string $date
     * @return $this
     */
    public function addReminderDateFilter($date)
    {
        $this->addFieldToFilter('reminder_date', $date);
        return $this;
    }
} 