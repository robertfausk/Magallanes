<?php
class Mage_Task_BuiltIn_Scm_ChangeBranch
    extends Mage_Task_TaskAbstract
{
	protected static $_startingBranch = 'master';
    private $_name = 'SCM Changing branch [built-in]';

    public function getName()
    {
        return $this->_name;
    }

    public function init()
    {
        switch ($this->getConfig()->general('scm')) {
            case 'git':
                $this->_name = 'SCM Changing branch (GIT) [built-in]';
                break;

            case 'svn':
                $this->_name = 'SCM Changing branch (Subversion) [built-in]';
                break;
        }
    }

    public function run()
    {
        switch ($this->getConfig()->general('scm')) {
            case 'git':
            	if ($this->getParameter('_changeBranchRevert', false)) {
            		$command = 'git checkout ' . self::$_startingBranch;
            		$result = $this->_runLocalCommand($command);

            	} else {
            		$command = 'git branch | grep \'*\' | cut -d\' \' -f 2';
            		$currentBranch = 'master';
            		$result = $this->_runLocalCommand($command, $currentBranch);

            		$scmData = $this->getConfig()->deployment('scm', false);
            		if ($result && is_array($scmData) && isset($scmData['branch'])) {
            			$branch = $this->getParameter('branch', $scmData['branch']);
            			$command = 'git checkout ' . $branch;
            			$result = $this->_runLocalCommand($command);

            			self::$_startingBranch = $currentBranch;

            		} else {
            			throw new Mage_Task_SkipException;
            		}
            	}


                break;

            default:
                throw new Mage_Task_SkipException;
                break;
        }


        $this->getConfig()->reload();

        return $result;
    }
}