<?php
/**
 * Product Reminder Observer for Product Deletion
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Vendor\ProductReminder\Model\ResourceModel\Reminder\CollectionFactory;
use Vendor\ProductReminder\Api\ReminderRepositoryInterface;

class DeleteProductReminders implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * @var ReminderRepositoryInterface
     */
    private $reminderRepository;

    /**
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param ReminderRepositoryInterface $reminderRepository
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        ReminderRepositoryInterface $reminderRepository
    ) {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->reminderRepository = $reminderRepository;
    }

    /**
     * Delete all reminders for a product when the product is deleted
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $productId = $product->getId();
            
            // Get all reminders for this product
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('product_id', $productId);
            
            $reminderIds = $collection->getAllIds();
            $count = count($reminderIds);
            
            if ($count > 0) {
                foreach ($reminderIds as $reminderId) {
                    $this->reminderRepository->deleteById($reminderId);
                }
                
                $this->logger->info(sprintf(
                    'Product Reminder: Deleted %d reminders for product %d (%s)',
                    $count,
                    $productId,
                    $product->getSku()
                ));
            }
        } catch (\Exception $e) {
            $this->logger->critical(
                'Error deleting product reminders: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
} 