<?php
/**
 * Product Reminder Model
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Model;

use Magento\Framework\Model\AbstractModel;
use Vendor\ProductReminder\Api\Data\ReminderInterface;
use Vendor\ProductReminder\Model\ResourceModel\Reminder as ReminderResource;

class Reminder extends AbstractModel implements ReminderInterface
{
    /**
     * Status constants
     */
    const STATUS_PENDING = 'Pending';
    const STATUS_SENT = 'Sent';
    
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ReminderResource::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getReminderDate()
    {
        return $this->getData(self::REMINDER_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setReminderDate($reminderDate)
    {
        return $this->setData(self::REMINDER_DATE, $reminderDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
    
    /**
     * Set reminder as sent
     *
     * @return $this
     */
    public function markAsSent()
    {
        $this->setStatus(self::STATUS_SENT);
        return $this;
    }
    
    /**
     * Check if reminder is pending
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() === self::STATUS_PENDING;
    }
} 