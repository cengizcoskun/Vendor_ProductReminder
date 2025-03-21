<?php
/**
 * Product Reminder Advance Notice Cron Job
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Vendor\ProductReminder\Api\ReminderRepositoryInterface;
use Vendor\ProductReminder\Model\Email\Sender;
use Vendor\ProductReminder\Model\ResourceModel\Reminder\CollectionFactory;

class SendAdvanceNotice
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
     * @var TimezoneInterface
     */
    private $timezone;
    
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
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param Sender $sender
     * @param ReminderRepositoryInterface $reminderRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Sender $sender,
        ReminderRepositoryInterface $reminderRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->sender = $sender;
        $this->reminderRepository = $reminderRepository;
    }

    /**
     * Execute cron job to send advance notice emails (1 week before reminder date)
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
        
        $this->logger->info('Product Reminder: Starting to send advance notice emails');
        
        // Calculate the date that is 7 days from now
        $oneWeekLater = $this->timezone->date()
            ->add(new \DateInterval('P7D'))
            ->format('Y-m-d');
        
        // Get all pending reminders for one week from now
        $collection = $this->collectionFactory->create();
        $collection->addPendingFilter()
            ->addReminderDateFilter($oneWeekLater);
        
        $remindersProcessed = 0;
        $remindersSent = 0;
        
        foreach ($collection as $reminder) {
            $remindersProcessed++;
            
            // Send advance notice email
            $emailSent = $this->sender->sendAdvanceNoticeEmail($reminder);
            
            if ($emailSent) {
                $remindersSent++;
            }
        }
        
        $this->logger->info(sprintf(
            'Product Reminder: Processed %d advance notices, successfully sent %d emails',
            $remindersProcessed,
            $remindersSent
        ));
    }
} 