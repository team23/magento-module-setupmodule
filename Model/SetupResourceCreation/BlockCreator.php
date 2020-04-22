<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

/**
 * Class BlockCreator
 *
 * @package Team23\SetupModule\Setup
 */
class BlockCreator implements CreatorInterface
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;
    /**
     * @var \Magento\Cms\Model\BlockRepository
     */
    protected $blockRepository;

    /**
     * BlockCreator constructor.
     *
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Model\BlockRepository $blockRepository
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\BlockRepository $blockRepository
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(array $data): void
    {
        if (!isset($data['identifier'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__("The xml tag 'identifier' may not be empty"));
        }

        if (!isset($data['title'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__("The xml tag 'title' may not be empty"));
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
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
