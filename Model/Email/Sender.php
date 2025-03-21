<?php
/**
 * Product Reminder Email Sender
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Model\Email;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Vendor\ProductReminder\Api\Data\ReminderInterface;

class Sender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Send reminder email
     *
     * @param ReminderInterface $reminder
     * @return bool
     */
    public function sendReminderEmail(ReminderInterface $reminder)
    {
        try {
            $this->inlineTranslation->suspend();
            
            // Get product and customer information
            $product = $this->productRepository->getById($reminder->getProductId());
            $customer = $this->customerRepository->getById($reminder->getCustomerId());
            
            // Get email configuration
            $senderEmail = $this->scopeConfig->getValue(
                'product_reminder/general/sender_email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            
            $defaultMessage = $this->scopeConfig->getValue(
                'product_reminder/general/default_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            
            // Prepare email template variables
            $templateVars = [
                'product_name' => $product->getName(),
                'customer_name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'reminder_message' => $defaultMessage,
                'store' => $this->storeManager->getStore()
            ];
            
            $store = $this->storeManager->getStore();
            
            // Build email transport
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('product_reminder_email_template')
                ->setTemplateOptions(['area' => 'frontend', 'store' => $store->getId()])
                ->setTemplateVars($templateVars)
                ->setFrom(['email' => $senderEmail, 'name' => 'Product Reminder'])
                ->addTo($customer->getEmail(), $customer->getFirstname() . ' ' . $customer->getLastname())
                ->getTransport();
            
            // Send email
            $transport->sendMessage();
            
            $this->inlineTranslation->resume();
            
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->inlineTranslation->resume();
            return false;
        }
    }

    /**
     * Send advance notice email (one week before reminder date)
     *
     * @param ReminderInterface $reminder
     * @return bool
     */
    public function sendAdvanceNoticeEmail(ReminderInterface $reminder)
    {
        try {
            $this->inlineTranslation->suspend();
            
            // Get product and customer information
            $product = $this->productRepository->getById($reminder->getProductId());
            $customer = $this->customerRepository->getById($reminder->getCustomerId());
            
            // Get email configuration
            $senderEmail = $this->scopeConfig->getValue(
                'product_reminder/general/sender_email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            
            $defaultMessage = $this->scopeConfig->getValue(
                'product_reminder/general/default_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            
            // Prepare email template variables
            $templateVars = [
                'product_name' => $product->getName(),
                'customer_name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'reminder_message' => $defaultMessage,
                'reminder_date' => $reminder->getReminderDate(),
                'store' => $this->storeManager->getStore()
            ];
            
            $store = $this->storeManager->getStore();
            
            // Build email transport
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('product_reminder_advance_notice_template')
                ->setTemplateOptions(['area' => 'frontend', 'store' => $store->getId()])
                ->setTemplateVars($templateVars)
                ->setFrom(['email' => $senderEmail, 'name' => 'Product Reminder'])
                ->addTo($customer->getEmail(), $customer->getFirstname() . ' ' . $customer->getLastname())
                ->getTransport();
            
            // Send email
            $transport->sendMessage();
            
            $this->inlineTranslation->resume();
            
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->inlineTranslation->resume();
            return false;
        }
    }
} 