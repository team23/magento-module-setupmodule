<?php

namespace Team23\SetupModule\Setup;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class BlockCreator
 * @package Team23\SetupModule\Setup
 */
class PageCreator
{
    /** @var PageFactory\ */
    protected $pageFactory;

    /** @var PageRepository */
    protected $pageRepository;

    /**
     * BlockCreator constructor.
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
     * @param $identifier
     * @param $content
     * @param string $title
     * @param string $contentHeading
     * @param array $stores
     * @param int $isActive
     * @param string $pageLayout
     * @return \Magento\Cms\Model\Page
     * @throws CouldNotSaveException
     */
    public function save($identifier, $title, $content = '', $contentHeading = '', $stores = [0], $isActive = 1, $pageLayout = '1column')
    {
        try {
            $page = $this->pageRepository->getById($identifier);
            $page->setTitle($title)
                ->setContent($content)
                ->setContentHeading($contentHeading)
                ->setStores($stores)
                ->setIsActive($isActive)
                ->setPageLayout($pageLayout)
                ->save();

            return $this->pageRepository->save($page);
        } catch (NoSuchEntityException $e) {
            $page = [
                'identifier' => $identifier,
                'title' => $title,
                'content' => $content,
                'content_heading' => $contentHeading,
                'stores' => $stores,
                'is_active' => $isActive,
                'page_layout' => $pageLayout
            ];
            $page = $this->pageFactory->create()->setData($page);

            return $this->pageRepository->save($page);
        }
    }
}
