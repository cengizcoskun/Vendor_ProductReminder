<?php
/**
 * Product Reminder Cron Job
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Vendor\ProductReminder\Api\ReminderRepositoryInterface;
use Vendor\ProductReminder\Model\Email\Sender;
use Vendor\ProductReminder\Model\ResourceModel\Reminder\CollectionFactory;

class SendReminders
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * @var Sender
     */
    private $sender;
    
    /**
     * @var ReminderRepositoryInterface
     */
    private $reminderRepository;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param Sender $sender
     * @param ReminderRepositoryInterface $reminderRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Sender $sender,
        ReminderRepositoryInterface $reminderRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->sender = $sender;
        $this->reminderRepository = $reminderRepository;
    }

    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute()
    {
        // Check if module is enabled
        $isEnabled = $this->scopeConfig->getValue(
            'product_reminder/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        if (!$isEnabled) {
            return;
        }
        
        $this->logger->info('Product Reminder: Starting to send reminder emails');
        
        // Get today's date
        $today = $this->dateTime->date('Y-m-d');
        
        // Get all pending reminders for today
        $collection = $this->collectionFactory->create();
        $collection->addPendingFilter()
            ->addReminderDateFilter($today);
        
        $remindersProcessed = 0;
        $remindersSent = 0;
        
        foreach ($collection as $reminder) {
            $remindersProcessed++;
            
            // Send email
            $emailSent = $this->sender->sendReminderEmail($reminder);
            
            if ($emailSent) {
                // Update reminder status
                $reminder->markAsSent();
                $this->reminderRepository->save($reminder);
                $remindersSent++;
            }
        }
        
        $this->logger->info(sprintf(
            'Product Reminder: Processed %d reminders, successfully sent %d emails',
            $remindersProcessed,
            $remindersSent
        ));
    }
} 