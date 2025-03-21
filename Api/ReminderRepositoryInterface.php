<?php
/**
 * Product Reminder Repository Interface
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vendor\ProductReminder\Api\Data\ReminderInterface;

interface ReminderRepositoryInterface
{
    /**
     * Save reminder
     *
     * @param ReminderInterface $reminder
     * @return ReminderInterface
     * @throws LocalizedException
     */
    public function save(ReminderInterface $reminder);

    /**
     * Get reminder by ID
     *
     * @param int $id
     * @return ReminderInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get reminders by customer ID
     *
     * @param int $customerId
     * @return \Vendor\ProductReminder\Api\Data\ReminderInterface[]
     */
    public function getByCustomerId($customerId);

    /**
     * Delete reminder
     *
     * @param ReminderInterface $reminder
     * @return bool
     * @throws LocalizedException
     */
    public function delete(ReminderInterface $reminder);

    /**
     * Delete reminder by ID
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($id);

    /**
     * Get reminders by criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Vendor\ProductReminder\Api\Data\ReminderInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
} 