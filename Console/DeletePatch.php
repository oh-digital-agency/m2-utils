<?php

declare(strict_types=1);

namespace OH\Utils\Console;

use Magento\Framework\App\Area;
use Magento\Framework\Setup\Patch\PatchHistory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeletePatch
 * @package OH\Utils\Console
 */
class DeletePatch extends Command
{
    const PATCH_NAME = 'name';

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
        $this->setName('oh:patch:delete')
            ->setDescription('Delete patch by name')
            ->setDefinition([]);
        $this->addArgument(
            self::PATCH_NAME,
            InputArgument::REQUIRED,
            'Patch name, ie: "OH\Module\Setup\Patch\Data\MyPatch" (Make sure is between quotes)'
        );
        parent::configure();
    }

    /**
     * Delete patch by name
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $patchName = str_replace("\\", "\\\\", $input->getArgument(self::PATCH_NAME));
            $output->writeln(sprintf('<info>Deleting patch %s</info>', $patchName));
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
            $query = sprintf("DELETE FROM %s WHERE patch_name = '%s'", PatchHistory::TABLE_NAME, $patchName);
            $this->resource->getConnection()->query($query);
            $output->writeln('<info>' . 'Patch deleted' . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
