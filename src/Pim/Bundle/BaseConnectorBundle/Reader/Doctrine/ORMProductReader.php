<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel as ChannelConstraint;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reads products one by one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMProductReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /**
     * @var int
     */
    protected $limit = 10;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Execution"})
     * @ChannelConstraint
     */
    protected $channel;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @var AbstractQuery
     */
    protected $query;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var null|integer[]
     */
    protected $ids = null;

    /**
     * @var \ArrayIterator
     */
    protected $products;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * @var CompletenessManager
     */
    protected $completenessManager;

    /**
     * @var MetricConverter
     */
    protected $metricConverter;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var bool
     */
    protected $missingCompleteness;

    /**
     * @param ProductRepositoryInterface $repository
     * @param ChannelRepositoryInterface $channelRepository
     * @param CompletenessManager        $completenessManager
     * @param MetricConverter            $metricConverter
     * @param EntityManager              $entityManager
     * @param bool                       $missingCompleteness
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        EntityManager $entityManager,
        $missingCompleteness = true
    ) {
        $this->entityManager       = $entityManager;
        $this->repository          = $repository;
        $this->channelRepository   = $channelRepository;
        $this->completenessManager = $completenessManager;
        $this->metricConverter     = $metricConverter;
        $this->products            = new \ArrayIterator();
        $this->missingCompleteness = $missingCompleteness;
    }

    /**
     * Set query used by the reader
     *
     * @param AbstractQuery $query
     *
     * @throws \InvalidArgumentException
     */
    public function setQuery(AbstractQuery $query)
    {
        $this->query = $query;
    }

    /**
     * Get query to execute
     *
     * @return AbstractQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $product = null;

        if (!$this->products->valid()) {
            $this->products = $this->getNextProducts();
        }

        if (null !== $this->products) {
            $product = $this->products->current();
            $this->products->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        if (null !== $product) {
            $this->metricConverter->convert($product, $this->channel);
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelRepository->getLabelsIndexedByCode(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->query = null;
        $this->entityManager->clear();
        $this->ids = null;
        $this->offset = 0;
        $this->products = new \ArrayIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param int $limit
     *
     * @return ORMProductReader
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get ids of products which are completes and in channel
     *
     * @return array
     */
    protected function getIds()
    {
        if (!is_object($this->channel)) {
            $this->channel = $this->channelRepository->findOneByIdentifier($this->channel);
        }

        if ($this->missingCompleteness) {
            $this->completenessManager->generateMissingForChannel($this->channel);
        }

        $this->query = $this->repository
            ->buildByChannelAndCompleteness($this->channel);

        $rootAlias = current($this->query->getRootAliases());
        $rootIdExpr = sprintf('%s.id', $rootAlias);

        $from = current($this->query->getDQLPart('from'));

        $this->query
            ->select($rootIdExpr)
            ->resetDQLPart('from')
            ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
            ->groupBy($rootIdExpr);

        $results = $this->query->getQuery()->getArrayResult();

        return array_keys($results);
    }

    /**
     * Get next products batch from DB
     *
     * @return \ArrayIterator
     */
    protected function getNextProducts()
    {
        $this->entityManager->clear();
        $products = null;

        if (null === $this->ids) {
            $this->ids = $this->getIds();
        }

        $currentIds = array_slice($this->ids, $this->offset, $this->limit);

        if (!empty($currentIds)) {
            $items = $this->repository->findByIds($currentIds);
            $products = new \ArrayIterator($items);
            $this->offset += $this->limit;
        }

        return $products;
    }
}
