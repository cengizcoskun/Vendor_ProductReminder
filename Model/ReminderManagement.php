<?php
/**
 * Product Reminder Management
 *
 * @category  Vendor
 * @package   Vendor_ProductReminder
 * @author    Yasin Cengiz Coşkun - Elryan
 */

namespace Vendor\ProductReminder\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Vendor\ProductReminder\Api\Data\ReminderInterface;
use Vendor\ProductReminder\Api\ReminderManagementInterface;
use Vendor\ProductReminder\Api\ReminderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Vendor\ProductReminder\Model\ReminderFactory;

class ReminderManagement implements ReminderManagementInterface
{
    /**
     * @var ReminderRepositoryInterface
     */
    private $reminderRepository;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var ReminderFactory
     */
    private $reminderFactory;
    
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param ReminderRepositoryInterface $reminderRepository
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param ReminderFactory $reminderFactory
     * @param UserContextInterface $userContext
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        ReminderRepositoryInterface $reminderRepository,
        DateTime $dateTime,
        LoggerInterface $logger,
        ReminderFactory $reminderFactory,
        UserContextInterface $userContext,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->reminderRepository = $reminderRepository;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->reminderFactory = $reminderFactory;
        $this->userContext = $userContext;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setReminder($customerId, $productId, $reminderDate, $status = null)
    {
        try {
            // Check authorization for API request
            $this->validateCustomerAccess($customerId);
            
            // Validate product exists
            $this->validateProduct($productId);
            
            // Create new reminder
            $reminder = $this->reminderFactory->create();
            
            // Set values
            $reminder->setCustomerId($customerId);
            $reminder->setProductId($productId);
            $reminder->setReminderDate($reminderDate);
            
            // Set status if provided, otherwise use default
            if ($status !== null) {
                $reminder->setStatus($status);
            } else {
                $reminder->setStatus(Reminder::STATUS_PENDING);
            }
            
            // Log for debugging
            $this->logger->debug(
                'Creating reminder',
                [
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'reminder_date' => $reminderDate,
                    'status' => $status ?? Reminder::STATUS_PENDING
                ]
            );
            
            // Validate reminder date is in the future
            $reminderTimestamp = strtotime($reminderDate);
            $currentTimestamp = strtotime($this->dateTime->date('Y-m-d'));
            
            if ($reminderTimestamp < $currentTimestamp) {
                throw new LocalizedException(__('Reminder date must be in the future.'));
            }
            
            // Save reminder and return
            return $this->reminderRepository->save($reminder);
        } catch (\Exception $e) {
            $this->logger->critical('Error saving reminder: ' . $e->getMessage(), ['exception' => $e]);
            throw new LocalizedException(__('Could not save the reminder: %1', $e->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateReminder($id, $customerId, $productId, $reminderDate, $status = null)
    {
        try {
            // Check authorization for API request
            $this->validateCustomerAccess($customerId);
            
            // Validate product exists
            $this->validateProduct($productId);
            
            // Get existing reminder by ID
            $reminder = $this->reminderRepository->getById($id);
            
            // Verify customer owns this reminder
            if ($reminder->getCustomerId() != $customerId) {
                throw new LocalizedException(__('You are not authorized to update this reminder.'));
            }
            
            // Update values
            $reminder->setCustomerId($customerId);
            $reminder->setProductId($productId);
            $reminder->setReminderDate($reminderDate);
            
            // Update status if provided
            if ($status !== null) {
                $reminder->setStatus($status);
            }
            
            // Log for debugging
            $this->logger->debug(
                'Updating reminder',
                [
                    'reminder_id' => $id,
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'reminder_date' => $reminderDate,
                    'status' => $status ?? $reminder->getStatus()
                ]
            );
            
            // Validate reminder date is in the future
            $reminderTimestamp = strtotime($reminderDate);
            $currentTimestamp = strtotime($this->dateTime->date('Y-m-d'));
            
            if ($reminderTimestamp < $currentTimestamp) {
                throw new LocalizedException(__('Reminder date must be in the future.'));
            }
            
            // Save updated reminder and return
            return $this->reminderRepository->save($reminder);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Validate that the API user has permission to access this customer's data
     * and that the customer exists in the system
     *
     * @param int $customerId
     * @throws LocalizedException
     * @return bool
     */
    private function validateCustomerAccess($customerId)
    {
        try {
            // If userContext is null, always throw an error when trying to access customer data
            if (!$this->userContext) {
                $this->logger->critical('UserContext not available');
                throw new LocalizedException(__('Authorization validation is not available.'));
            }
            
            // Validate that the customer exists
            if (!$customerId) {
                throw new LocalizedException(__('Customer ID is required.'));
            }
            
            try {
                // Check if customer exists
                $this->customerRepository->getById($customerId);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('The specified customer does not exist.'));
            }

            // Get user identity
            $userId = $this->userContext->getUserId();
            $userType = $this->userContext->getUserType();
            
            // Check if user type is customer
            if ($userType === UserContextInterface::USER_TYPE_CUSTOMER) {
                // Check if current customer ID matches the requested customer ID
                if ((int)$userId !== (int)$customerId) {
                    throw new LocalizedException(__('You can only manage reminders for your own customer account.'));
                }
            } elseif ($userType === UserContextInterface::USER_TYPE_ADMIN) {
                // Admin users can access all customer data, no additional check needed
                return true;
            } elseif ($userType === UserContextInterface::USER_TYPE_INTEGRATION) {
                // Controls for when Integration token is used
                // Special permissions for integration APIs can be checked here
                return true;
            } else {
                // Unrecognized user type
                throw new LocalizedException(__('Unauthorized access.'));
            }
            
            return true;
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred while validating authorization: %1', $e->getMessage()));
        }
    }

    /**
     * Validate that the product exists
     *
     * @param int $productId
     * @throws LocalizedException
     * @return bool
     */
    private function validateProduct($productId)
    {
        try {
            if (!$productId) {
                throw new LocalizedException(__('Product ID is required.'));
            }
            
            // Try to load the product to verify it exists
            $product = $this->productRepository->getById($productId);
            
            // Check if product is enabled (status = 1 means enabled)
            if (!$product->getStatus() || $product->getStatus() != 1) {
                throw new LocalizedException(__('Product is not available.'));
            }
            
            return true;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical('Product not found: ' . $e->getMessage());
            throw new LocalizedException(__('The product with ID "%1" does not exist.', $productId));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical('Error validating product: ' . $e->getMessage());
            throw new LocalizedException(__('An error occurred while validating the product: %1', $e->getMessage()));
        }
    }
} 