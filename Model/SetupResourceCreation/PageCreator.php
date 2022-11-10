<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class BlockCreator
 *
 * @package Team23\SetupModule\Setup
 */
class PageCreator implements CreatorInterface
{
    /**
     * @var PageFactory
     */
    protected PageFactory $pageFactory;

    /** @var PageRepository */
    protected PageRepository $pageRepository;

    /**
     * BlockCreator constructor.
     *
     * @param PageFactory $pageFactory
     * @param PageRepository $pageRepository
     */
    public function __construct(
        PageFactory $pageFactory,
        PageRepository $pageRepository
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
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
     * Save function
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
        $contentHeading = $data['content_heading'] ?? '';
        $pageLayout = $data['page_layout'] ?? '1column';
        $storeId = (int)($data['store_id'] ?? 0);
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
