<?php
/**
 * Product Reminder Management Interface
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Api;

interface ReminderManagementInterface
{
    /**
     * Set reminder for product
     *
     * @param int $customerId Customer ID
     * @param int $productId Product ID
     * @param string $reminderDate Reminder date in format YYYY-MM-DD
     * @param string $status Reminder status (optional)
     * @return \Vendor\ProductReminder\Api\Data\ReminderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setReminder($customerId, $productId, $reminderDate, $status = null);

    /**
     * Update existing reminder
     *
     * @param int $id Reminder ID
     * @param int $customerId Customer ID
     * @param int $productId Product ID
     * @param string $reminderDate Reminder date in format YYYY-MM-DD
     * @param string $status Reminder status (optional)
     * @return \Vendor\ProductReminder\Api\Data\ReminderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateReminder($id, $customerId, $productId, $reminderDate, $status = null);
} 