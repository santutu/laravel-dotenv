<?php


namespace Santutu\LaravelDotEnv\Tests;


use Illuminate\Support\Facades\Artisan;
use Santutu\LaravelDotEnv\DotEnv;
use Santutu\LaravelDotEnv\Facade;
use Santutu\LaravelDotEnv\ServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;
use Webmozart\PathUtil\Path;

class DotEnvTest extends \Orchestra\Testbench\TestCase
{

    public function test_dot_env()
    {
        $dotEnvFilePath = app()->environmentFilePath();
        if (file_exists($dotEnvFilePath)) {
            unlink($dotEnvFilePath);
        }
        $dotEnv = new DotEnv($dotEnvFilePath);
        $this->assertTrue(file_exists($dotEnvFilePath));
        $this->assertNull($dotEnv->get('TEST'));
        $this->assertEquals(null, $dotEnv->getNewValue());
        $this->assertEquals(null, $dotEnv->getOldValue());

        //can set
        $dotEnv->set('TEST', 'value');
        $this->assertEquals('value', $dotEnv->getNewValue());
        $this->assertEquals(null, $dotEnv->getOldValue());
        $this->assertEquals($dotEnv->get('TEST'), 'value');
        $this->assertEquals($dotEnv->getKey(), 'TEST');

        $dotEnv->set('TEST', 'value2');
        $this->assertEquals('value2', $dotEnv->getNewValue());
        $this->assertEquals('value', $dotEnv->getOldValue());
        $this->assertEquals($dotEnv->get('TEST'), 'value2');
        $this->assertEquals($dotEnv->getKey(), 'TEST');

        //can set null
        $dotEnv->set('TEST', null);
        $this->assertEquals(null, $dotEnv->getNewValue());
        $this->assertEquals('value2', $dotEnv->getOldValue());
        $this->assertNull($dotEnv->get('TEST'));

        //can set empty
        $dotEnv->set('TEST', '');
        $this->assertEquals('', $dotEnv->getNewValue());
        $this->assertEquals(null, $dotEnv->getOldValue());
        $this->assertNotNull($dotEnv->get('TEST'));
        $this->assertEquals('', $dotEnv->get('TEST'));

        //can delete
        $this->assertEquals("TEST=", $dotEnv->delete('TEST'));;
        $this->assertEquals(null, $dotEnv->delete('TEST'));;
        $this->assertNull($dotEnv->get('TEST'));

        //conversions
        $conversionMap = ['null' => null,
            'true' => true,
            'false' => false];
        foreach ($conversionMap as $value) {
            $dotEnv->set('TEST', $value);
            $this->assertEquals($value, $dotEnv->get('TEST'));
        }

        foreach ($conversionMap as $key => $value) {
            $dotEnv->set('TEST', $key);
            $this->assertEquals($value, $dotEnv->get('TEST'));
        }

        foreach ($conversionMap as $key => $value) {
            $dotEnv->set('TEST', mb_strtoupper($key));
            $this->assertEquals($value, $dotEnv->get('TEST'));
        }

        foreach ($conversionMap as $key => $value) {
            $dotEnv->set('TEST', mb_strtolower($key));
            $this->assertEquals($value, $dotEnv->get('TEST'));
        }

        Artisan::call('env:set TEST false', [], new ConsoleOutput());
        $this->assertFalse($dotEnv->get('TEST'));
        Artisan::call('env:set TEST null', [], new ConsoleOutput());
        $this->assertNull($dotEnv->get('TEST'));

        //

        if (file_exists($dotEnvFilePath)) {
            unlink($dotEnvFilePath);
        }

        //can copy
        if (!file_exists($dotEnvFilePath)) {
            $f = fopen('.env.example', 'w');
            fclose($f);
        }
        $dotEnv->copy('.env.example', $dotEnvFilePath);
        $dotEnv->copy('.env.example', $dotEnvFilePath);
        $this->assertEquals(Path::getDirectory($dotEnvFilePath), Path::getDirectory($dotEnv->dotEnvFilePath));
        $this->assertEquals(Path::getFilename($dotEnvFilePath), Path::getFilename($dotEnv->dotEnvFilePath));
        $this->assertTrue(file_exists($dotEnvFilePath));

        //artisan set
        Artisan::call('env:set TEST value');
        Artisan::call('env:set TEST_T value2');
        $this->assertEquals('value', $dotEnv->get('TEST'));
        $this->assertEquals('value2', $dotEnv->get('TEST_T'));

        Artisan::call('env:set TEST');
        $this->assertEquals('', $dotEnv->get('TEST'));

        //artisan get
        Artisan::call('env:get TEST TEST_T', [], new ConsoleOutput());

        //artisan delete
        Artisan::call('env:delete TEST TEST_T', [], new ConsoleOutput());
        $this->assertNull($dotEnv->get('TEST'));
        $this->assertNull($dotEnv->get('TEST_T'));

        //facade
        (new DotEnv)->copy('.env.example','.env');
        (new DotEnv)->copy('.env', 'prod');
        $this->assertNull(\DotEnv::get('TEST'));
        $this->assertEquals('.env', \DotEnv::getDotEnvFilePath());
        //
        Artisan::call('env:set TEST value55 --env=.env.test', []);
        $dotEnv->load('.env.test');
        $this->assertEquals('value55', $dotEnv->get('TEST'));

        Artisan::call('env:get TEST --env=.env.test', [], new ConsoleOutput());
        Artisan::call('env:delete TEST --env=.env.test', [], new ConsoleOutput());
        $this->assertEquals(null, $dotEnv->get('TEST'));


        //auto prefix
        $prodDotEnv = new DotEnv('prod');
        $this->assertEquals('.env.prod', $prodDotEnv->dotEnvFilePath);

        //use
        $prodDotEnv->set('TEST', 'PROD');
        $dotEnv->copy('prod');
        $this->assertEquals('PROD', $dotEnv->get('TEST'));
        $this->assertTrue(file_exists('.env.temp'));

        Artisan::call('env:set TEST null');
//        Artisan::call('env:copy prod');
//        $this->assertEquals('PROD', $dotEnv->get('TEST'));

        //space
        $dotEnv->set('TEST', 'TE ST');
        $this->assertEquals('TE ST', $dotEnv->get('TEST'));

        //escape
        $letters = ['TE ST\"', 'TEST\"', '\"TEST\"', 'TE \"ST'];
        foreach ($letters as $letter) {
            $dotEnv->set('TEST', $letter);
            $this->assertEquals($letter, $dotEnv->get('TEST'));
        }

        //default
        $this->assertEquals('zxc', $dotEnv->get('ASD', 'zxc'));

        //copy by dotEnv
        $prodDotEnv->set('TEST', 'Asd');
        $dotEnv->copyByIns($prodDotEnv);
        $this->assertEquals($prodDotEnv->get('TEST'), $dotEnv->get('TEST'));
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'DotEnv' => Facade::class
        ];
    }
}