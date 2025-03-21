<?php
/**
 * Product Reminder Repository
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vendor\ProductReminder\Api\Data\ReminderInterface;
use Vendor\ProductReminder\Api\ReminderRepositoryInterface;
use Vendor\ProductReminder\Model\ResourceModel\Reminder as ReminderResource;
use Vendor\ProductReminder\Model\ResourceModel\Reminder\CollectionFactory;

class ReminderRepository implements ReminderRepositoryInterface
{
    /**
     * @var ReminderResource
     */
    private $reminderResource;

    /**
     * @var ReminderFactory
     */
    private $reminderFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ReminderResource $reminderResource
     * @param ReminderFactory $reminderFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ReminderResource $reminderResource,
        ReminderFactory $reminderFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->reminderResource = $reminderResource;
        $this->reminderFactory = $reminderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ReminderInterface $reminder)
    {
        try {
            if (!$reminder instanceof \Magento\Framework\Model\AbstractModel) {
                throw new CouldNotSaveException(__('Invalid reminder model type.'));
            }
            
            $this->reminderResource->save($reminder);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $reminder;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $reminder = $this->reminderFactory->create();
        $this->reminderResource->load($reminder, $id);
        if (!$reminder->getId()) {
            throw new NoSuchEntityException(__('The reminder with ID "%1" doesn\'t exist.', $id));
        }
        return $reminder;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getByCustomerId($customerId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addCustomerFilter($customerId);
        return $collection->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ReminderInterface $reminder)
    {
        try {
            if (!$reminder instanceof \Magento\Framework\Model\AbstractModel) {
                throw new CouldNotDeleteException(__('Invalid reminder model type.'));
            }
            
            $this->reminderResource->delete($reminder);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
    
    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        return $collection->getItems();
    }
} 