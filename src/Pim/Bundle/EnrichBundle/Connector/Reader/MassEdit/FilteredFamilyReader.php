<?php

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredFamilyReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool */
    protected $isExecuted;

    /** @var ArrayCollection */
    protected $families;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var array */
    protected $filters;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
        $this->isExecuted = false;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $configuration = $this->getConfiguration();
        if (!$this->isExecuted) {
            $this->isExecuted = true;
            $this->families = $this->getFamilies($configuration['filters']);
        }

        $result = $this->families->current();

        if (!empty($result)) {
            $this->stepExecution->incrementSummaryInfo('read');
            $this->families->next();
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return ['filters' => []];
    }

    /**
     * @param array $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Get families with given $filters.
     * In this particular case, we'll only have 1 filter based on ids
     * (We don't have raw filters yet for family grid)
     *
     * @param array $filters
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getFamilies(array $filters)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);

        $filter = current($filters);
        $filter = $resolver->resolve($filter);

        $familiesIds = $filter['value'];

        return new ArrayCollection($this->familyRepository->findByIds($familiesIds));
    }
}
