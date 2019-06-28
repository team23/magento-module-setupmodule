<?php

namespace Team23\SetupModule\Setup;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveBlock
{

    /** @var BlockFactory */
    private $blockFactory;

    /** @var BlockRepository */
    private $blockRepository;

    /**
     * InstallData constructor.
     * @param BlockFactory $blockFactory
     * @param BlockRepository $blockRepository
     */
    public function __construct(BlockFactory $blockFactory, BlockRepository $blockRepository)
    {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @param $identifier
     * @param $content
     * @param string $title
     * @param array $stores
     * @param int $isActive
     * @return \Magento\Cms\Model\Block
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveBlock($identifier, $content, $title = '', $stores = [0], $isActive = 1)
    {
        try {
            $block = $this->blockRepository->getById($identifier);
            $block->setContent($content);
            $block->setTitle($title);
            $block->setStoreId($stores);
            $block->setIsActive($isActive);
            return $this->blockRepository->save($block);
        } catch (NoSuchEntityException $e) {
            $newBlock = [
                'identifier' => $identifier,
                'content' => $content,
                'title' => $title,
                'stores' => $stores,
                'is_active' => $isActive
            ];

            $newBlock = $this->blockFactory->create()->setData($newBlock);
            return $this->blockRepository->save($newBlock);
        }
    }
}
