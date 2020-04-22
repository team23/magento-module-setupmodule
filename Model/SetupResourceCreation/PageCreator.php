<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

/**
 * Class BlockCreator
 *
 * @package Team23\SetupModule\Setup
 */
class PageCreator implements CreatorInterface
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /** @var \Magento\Cms\Model\PageRepository */
    protected $pageRepository;

    /**
     * BlockCreator constructor.
     *
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\PageRepository $pageRepository
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
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
     * Save function
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
        $contentHeading = $data['content_heading'] ?? '';
        $pageLayout = $data['page_layout'] ?? '1column';
        $storeId = (int)($data['content'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 0);

        try {
            $page = $this->pageRepository->getById($identifier);
            $page
                ->setTitle($title)
                ->setContent($content)
                ->setContentHeading($contentHeading)
                ->setStoreId($storeId)
                ->setPageLayout($pageLayout)
                ->setIsActive($isActive);

            $this->pageRepository->save($page);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $newPage = [
                'identifier' => $identifier,
                'title' => $title,
                'content' => $content,
                'content_heading' => $contentHeading,
                'store_id' => $storeId,
                'is_active' => $isActive,
                'page_layout' => $pageLayout,
            ];
            $newPage = $this->pageFactory->create()->setData($newPage);
            $this->pageRepository->save($newPage);
        }
    }
}
