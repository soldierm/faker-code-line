<?php

namespace Who\FakerCodeLine\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Who\FakerCodeLine\Git\Git;
use Who\FakerCodeLine\Git\GitRepo;

class AutoCommitCommand extends Command
{
    /**
     * 命令
     *
     * @var string
     */
    protected static $defaultName = 'faker:commit';

    /**
     * 本地仓库地址，绝对路径
     *
     * @var string
     */
    private $local;

    /**
     * 代码模版，绝对路径
     *
     * @var string
     */
    private $template;

    /**
     * @var GitRepo
     */
    private $git;

    public function __construct($config = [])
    {
        parent::__construct();

        $this->local = $config['local'];
        $this->template = $config['template'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '<info>自动提交代码脚本启动成功</info>',
        ]);

        if (!$this->validate($input, $output)) {
            return Command::FAILURE;
        }

        try {
            $this->git = Git::open($this->local);
        } catch (Exception $exception) {
            $output->writeln([
                '<error>git配置错误：' . $exception->getMessage() . '</error>'
            ]);
            return Command::FAILURE;
        }

        if (!$this->ping($input, $output)) {
            return Command::FAILURE;
        }

        if (!$this->mockCode($input, $output)) {
            return Command::FAILURE;
        }

        if (!$this->commit($input, $output)) {
            return Command::FAILURE;
        }

        if (!$this->push($input, $output)) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function validate(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln([
            '<info>准备校验配置是否正确</info>',
        ]);

        if (empty($this->local)) {
            $output->writeln([
                '<error>没有配置local本地路径</error>',
            ]);

            return false;
        }

        if (empty($this->template)) {
            $output->writeln([
                '<error>没有配置template模版路径</error>',
            ]);

            return false;
        }

        $output->writeln([
            '<info>配置检测通过</info>',
        ]);

        return true;
    }

    private function ping(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln([
            '<info>准备测试是否可以连通remote</info>',
        ]);

        try {
            $fetch = $this->git->fetch();
            $output->writeln([
                '<info>成功连通remote</info>'
            ]);
            return true;
        } catch (Exception $exception) {
            $output->writeln([
                '<error>' . $exception->getMessage() . '</error>'
            ]);
            return false;
        }
    }

    private function mockCode(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln([
            '<info>准备生成代码文件</info>',
        ]);
        try {
            $code = file_get_contents($this->template['path']);
            $filename = date('YmdHis') . 'commit.' . $this->template['suffix'];
            file_put_contents($this->local . '/' . $filename, $code);
            $output->writeln([
                '<info>代码生成成功，文件名：' . $filename . '</info>',
            ]);
            return true;
        } catch (Exception $exception) {
            $output->writeln([
                '<error>代码生成失败：' . $exception->getMessage() . '</error>',
            ]);
            return false;
        }
    }

    private function commit(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln([
            '<info>准备commit代码</info>',
        ]);

        try {
            $this->git->add('.');
            $this->git->commit('代码提交，日期：' . date('Y-m-d'));
            $output->writeln([
                '<info>commit代码成功</info>',
            ]);
            return true;
        } catch (Exception $exception) {
            $output->writeln([
                '<error>commit代码失败：' . $exception->getMessage() . '</error>',
            ]);
            return false;
        }
    }

    private function push(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln([
            '<info>准备push代码到remote</info>',
        ]);

        try {
            $this->git->push('origin', 'master');
            $output->writeln([
                '<info>成功</info>push代码到remote</info>',
            ]);
            return true;
        } catch (Exception $exception) {
            $output->writeln([
                '<error>push代码失败：' . $exception->getMessage() . '</error>',
            ]);
            return false;
        }
    }
}