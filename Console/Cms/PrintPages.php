<?php

declare(strict_types=1);

namespace OH\Utils\Console\Cms;

use Magento\Framework\App\Area;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class PrintPages
 * @package OH\Utils\Console\Cms
 */
class PrintPages extends Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\State $appState,
        string $name = null
    ) {
        $this->resource = $resource;
        $this->appState = $appState;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('oh:cms:printall')
            ->setDescription('Print cms pages ids and identifiers')
            ->setDefinition([]);
        parent::configure();
    }

    /**
     * Print basic data of all available cms pages
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
            $io = new SymfonyStyle($input, $output);
            $this->printMainTitle($io);

            foreach ($this->getAllCms() as $page) {
                $this->printSection($io);
                $this->printInfo($io, $page);
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }

    private function getStoresByCms($pageId)
    {
        $storesQuery = sprintf('SELECT store_id FROM %s WHERE page_id = %s', 'cms_page_store', $pageId);
        return $this->resource->getConnection()->query($storesQuery)->fetchAll();
    }

    private function getAllCms()
    {
        $allCmsQuery = sprintf("SELECT creation_time,update_time,is_active,title,page_id,identifier FROM %s", 'cms_page');
        return $this->resource->getConnection()->query($allCmsQuery);
    }

    private function printMainTitle($io)
    {
        $io->title('Basic cms pages information');
    }

    private function printSection($io)
    {
        $io->section('Page details');
    }

    private function printInfo($io, $page)
    {
        $stores = $this->getStoresByCms($page['page_id']);
        $storesVal = implode(',', array_column($stores, 'store_id'));

        $io->table(
            ['Page ID', 'Identifier', 'Title', 'Is active', 'Created at', 'Last update', 'Stores'],
            [
                [$page['page_id'], $page['title'], $page['identifier'], $page['is_active'] ? 'Yes' : 'No', $page['creation_time'], $page['update_time'], $storesVal ?: '-']
            ]
        );
    }
}
