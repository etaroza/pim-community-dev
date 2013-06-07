<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Type;

use Symfony\Component\Security\Core\SecurityContext;

use Pim\Bundle\TranslationBundle\Form\Type\TranslatableFieldType;

use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;

use Symfony\Component\Form\Forms;

use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

use Pim\Bundle\ProductBundle\Form\Type\ProductAttributeType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeTypeTest extends AbstractFormTypeTest
{

    /**
     * @var \Pim\Bundle\ProductBundle\Form\Type\ProductAttributeType
     */
    protected $type;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    protected $form;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->markTestIncomplete('Either drop this test class or find a neat way to add entity form type support');
        parent::setUp();

        // Create a mock for the form and exclude the availableLocales and getAttributeTypeChoices methods
        $this->type = $this->getMock(
            'Pim\Bundle\ProductBundle\Form\Type\ProductAttributeType',
            array('addFieldAvailableLocales', 'getAttributeTypeChoices', 'addSubscriber')
        );
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('label', 'pim_translatable_field');
        $this->assertField('description', 'textarea');
        $this->assertField('variant', 'choice');
        $this->assertField('smart', 'checkbox');
        $this->assertField('useableAsGridColumn', 'checkbox');
        $this->assertField('useableAsGridFilter', 'checkbox');

        $this->assertField('group', 'entity');

        $this->assertAttributeType();

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_product_attribute', $this->form->getName());
    }

    /**
     * Assert field name and type
     * @param string $name Field name
     * @param string $type Field type alias
     */
    protected function assertField($name, $type)
    {
        $formType = $this->form->get($name);
        $this->assertInstanceOf('\Symfony\Component\Form\Form', $formType);
        $this->assertEquals($type, $formType->getConfig()->getType()->getInnerType()->getName());
    }

    /**
     * Assert attribute type data
     */
    protected function assertAttributeType()
    {
        $this->assertField('id', 'hidden');
        $this->assertField('code', 'text');
        $this->assertField('attributeType', 'choice');
        $this->assertField('required', 'checkbox');
    }
}
