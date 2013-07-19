<?php

namespace Touki\FTP\Tests;

use Touki\FTP\FTP;
use Touki\FTP\UploaderVoter;
use Touki\FTP\Model\File;

/**
 * Uploader voter test case for FTP uploaders
 *
 * @author Touki <g.vincendon@vithemis.com>
 */
class UploaderVoterFTPTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $wrapper = $this->getMockBuilder('Touki\FTP\FTPWrapper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->voter = new UploaderVoter;
        $this->voter->addDefaultFTPUploaders($wrapper);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Could not resolve an uploader with the given options
     */
    public function testVoteNoElection()
    {
        $local = __FILE__;
        $file  = new File('/foo');

        $this->voter->vote($file, $local);
    }

    public function testVoteElectFileUploader()
    {
        $local   = __FILE__;
        $file    = new File('/foo');
        $options = array(
            FTP::NON_BLOCKING => false
        );
        $uploader = $this->voter->vote($file, $local, $options);

        $this->assertInstanceOf('Touki\FTP\Uploader\FTP\FileUploader', $uploader);
    }

    public function testVoteElectResourceUploader()
    {
        $local   = fopen(__FILE__, 'r');
        $file    = new File('/foo');
        $options = array(
            FTP::NON_BLOCKING => false
        );
        $uploader = $this->voter->vote($file, $local, $options);

        $this->assertInstanceOf('Touki\FTP\Uploader\FTP\ResourceUploader', $uploader);

        fclose($local);
    }

    public function testVoteElectNbFileUploader()
    {
        $local   = __FILE__;
        $file    = new File('/foo');
        $options = array(
            FTP::NON_BLOCKING => true
        );
        $uploader = $this->voter->vote($file, $local, $options);

        $this->assertInstanceOf('Touki\FTP\Uploader\FTP\NbFileUploader', $uploader);
    }

    public function testVoteElectNbResourceUploader()
    {
        $local   = fopen(__FILE__, 'r');
        $file    = new File('/foo');
        $options = array(
            FTP::NON_BLOCKING => true
        );
        $uploader = $this->voter->vote($file, $local, $options);

        $this->assertInstanceOf('Touki\FTP\Uploader\FTP\NbResourceUploader', $uploader);

        fclose($local);
    }

    public function testVoteAppendAnotherFileUploader()
    {
        $local   = __FILE__;
        $file    = new File('/foo');
        $options = array(
            FTP::NON_BLOCKING => false
        );
        $mock = $this->getMock('Touki\FTP\UploaderVotableInterface');
        $mock
            ->expects($this->any())
            ->method('vote')
            ->will($this->returnValue(true))
        ;
        $this->voter->addVotable($mock, $prepend = false);

        $uploader = $this->voter->vote($file, $local, $options);

        $this->assertInstanceOf('Touki\FTP\Uploader\FTP\FileUploader', $uploader);
    }

    public function testVotePrependAnotherFileUploader()
    {
        $local   = __FILE__;
        $file    = new File('/foo');
        $options = array(
            FTP::NON_BLOCKING => false
        );
        $mock = $this->getMock('Touki\FTP\UploaderVotableInterface');
        $mock
            ->expects($this->once())
            ->method('vote')
            ->will($this->returnValue(true))
        ;
        $this->voter->addVotable($mock, $prepend = true);

        $uploader = $this->voter->vote($file, $local, $options);

        $this->assertSame($mock, $uploader);
    }
}