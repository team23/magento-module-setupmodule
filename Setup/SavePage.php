<?php

namespace Team23\SetupModule\Setup;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;

class SavePage
{

    /** @var PageFactory */
    private $pageFactory;

    /** @var PageRepository */
    private $pageRepository;

    /**
     * SavePage constructor.
     * @param PageFactory $pageFactory
     * @param PageRepository $pageRepository
     */
    public function __construct(PageFactory $pageFactory, PageRepository $pageRepository)
    {
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param $identifier
     * @param $content
     * @param string $title
     * @param array $stores
     * @param int $isActive
     * @param string $pageLayout
     * @return \Magento\Cms\Model\Page
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function savePage(
        $identifier,
        $content,
        $title = '',
        $contentHeading = '',
        $stores = [0],
        $isActive = 1,
        $pageLayout = '1column',
        $layoutUpdateXml = ''
    ) {
        try {
            $page = $this->pageRepository->getById($identifier);
            $page
                ->setTitle($title)
                ->setContentHeading($contentHeading)
                ->setIsActive($isActive)
                ->setPageLayout($pageLayout)
                ->setStores($stores)
                ->setContent($content)
                ->save();
            return $this->pageRepository->save($page);
        } catch (\Exception $e) {
            $page = [
                'identifier' => $identifier,
                'content' => $content,
                'title' => $title,
                'stores' => $stores,
                'is_active' => $isActive,
                'page_layout' => $pageLayout
            ];

            $page = $this->pageFactory->create()->setData($page);
            return $this->pageRepository->save($page);
        }
    }
}
