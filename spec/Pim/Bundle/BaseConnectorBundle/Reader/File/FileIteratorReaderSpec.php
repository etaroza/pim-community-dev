<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;
use Pim\Bundle\BaseConnectorBundle\Iterator\FileIteratorFactory;

class FileIteratorReaderSpec extends ObjectBehavior
{
    public function let(FileIteratorFactory $iteratorFactory)
    {
        $this->beConstructedWith($iteratorFactory, 'iterator_class', array('iterator_options'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Reader\File\FileIteratorReader');
    }

    public function it_calls_the_iterator_factory(FileIteratorFactory $iteratorFactory)
    {
        $values = array('value1', 'value2', 'value3');
        $iteratorFactory->create('iterator_class', 'file_path', array('iterator_options'))
            ->willReturn(new \ArrayIterator($values));

        $this->setFilePath('file_path');
        foreach ($values as $value) {
            $this->read()->shouldReturn($value);
        }
    }

    public function it_has_a_file_path_property()
    {
        $this->setFilePath('file_path');
        $this->getFilePath()->shouldReturn('file_path');
    }

    public function it_has_an_uploadable_property()
    {
        $this->shouldNotBeUploadAllowed();
        $this->setUploadAllowed(true);
        $this->shouldBeUploadAllowed();
        $this->setUploadAllowed(false);
        $this->shouldNotBeUploadAllowed();
    }

    public function it_has_file_upload_capability(File $file)
    {
        $file->getRealPath()->willReturn('file_path');
        $this->setUploadedFile($file);
        $this->getFilePath()->shouldReturn('file_path');
    }
}
