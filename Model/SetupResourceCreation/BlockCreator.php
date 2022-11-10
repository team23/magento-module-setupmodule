<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class BlockCreator
 *
 * @package Team23\SetupModule\Setup
 */
class BlockCreator implements CreatorInterface
{
    /**
     * @var BlockFactory
     */
    protected BlockFactory $blockFactory;
    /**
     * @var BlockRepository
     */
    protected BlockRepository $blockRepository;

    /**
     * BlockCreator constructor.
     *
     * @param BlockFactory $blockFactory
     * @param BlockRepository $blockRepository
     */
    public function __construct(
        BlockFactory $blockFactory,
        BlockRepository $blockRepository
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        if (!isset($data['identifier'])) {
            throw new LocalizedException(__("The xml tag 'identifier' may not be empty"));
        }

        if (!isset($data['title'])) {
            throw new LocalizedException(__("The xml tag 'title' may not be empty"));
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(array $data): void
    {
        $this->validate($data);

        $identifier = $data['identifier'];
        $title = $data['title'];
        $content = $data['content'] ?? '';
        $storeId = (int)($data['store_id'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 0);

        try {
            $block = $this->blockRepository->getById($identifier);
            $block
                ->setTitle($title)
                ->setContent($content)
                ->setStoreId($storeId)
                ->setIsActive($isActive);

            $this->blockRepository->save($block);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $newBlock = [
                'identifier' => $identifier,
                'title' => $title,
                'content' => $content,
                'store_id' => $storeId,
                'is_active' => $isActive,
            ];
            $newBlock = $this->blockFactory->create()->setData($newBlock);

            $this->blockRepository->save($newBlock);
        }
    }
}
