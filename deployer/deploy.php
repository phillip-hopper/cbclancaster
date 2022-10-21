<?php
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpStrFunctionsInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpIncludeInspection */
/** @noinspection PhpMethodParametersCountMismatchInspection */

namespace Deployer;

use Exception;
use Throwable;

require 'recipe/common.php';

// Project name
set('application', 'cbclancaster.org');

set('default_timeout', 1200);

// Project repository
set('repository', 'git@github.com:phillip-hopper/cbclancaster.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// we need sudo for cleanup
set('cleanup_use_sudo', true);
set('clear_use_sudo', true);

// Shared files/dirs between deploys
set('shared_files_ex', [
    ['config/configuration.php', 'joomla/configuration.php']
]);

set('shared_dirs_ex', [
    ['images', 'joomla/images'],
    ['files', 'joomla/files'],
	['cache', 'joomla/cache'],
	['media-cache', 'joomla/media/cache'],
	['tmp', 'joomla/tmp'],
	['admin-cache', '/joomla/administrator/cache'],
	['admin-logs', '/joomla/administrator/logs']
]);

set('clear_paths', ['joomla/cache/*', 'joomla/administrator/cache/*']);

// Writable dirs by web server
set('writable_dirs', ['cache', 'images', 'files', 'media-cache', 'tmp', 'admin-cache', 'admin-logs']);


// Hosts
set('default_stage', 'dev');
inventory('hosts.yml');


desc('Install the nodejs packages needed to compile sass and typescript files.');
task('deploy:npm', function () {
    run("cd {{release_path}} && npm {{npm_options}}");
});


task('deploy:compile:sass', function () {
    run("cd {{release_path}} && ./sass.sh");
})->desc('Compile the sass files into css.');


task('deploy:compile:typescript', function () {
    run("cd {{release_path}} && ./tsc.sh");
})->desc('Compile the typescript files into js.');


desc('Generate static pages.');
task('deploy:generate_static', function () {

    run("chmod -R 0755 {{release_path}}/public");
});


desc('Creating symlinks.');
task('deploy:create_symlinks', function () {

    // create link to misc files
    run("ln -sf {{release_path}}/resources/favicon.ico {{release_path}}/joomla/templates/rt_orion/favicon.ico");
});


desc('Creating symlinks for shared files and dirs using array instead of string');
task('deploy:shared_ex', function () {

    // NB: $dir is an array
    // $dir[0] is the name in $sharedPath
    // $dir[1] is the name in {{deploy_path}}

    $sharedPath = "{{deploy_path}}/shared";

    // Validate shared_dir, find duplicates
    foreach (get('shared_dirs_ex') as $a) {
        foreach (get('shared_dirs_ex') as $b) {
            if ($a[0] !== $b[0] && strpos(rtrim($a[0], '/') . '/', rtrim($b[0], '/') . '/') === 0) {
                if ($a[1] == $b[1]) {
	                /** @noinspection PhpUnhandledExceptionInspection */
	                throw new Exception("Can not share same dirs `$a[1]` and `$b[1]`.");
                }
            }
        }
    }

    foreach (get('shared_dirs_ex') as $dir) {

        // Check if shared dir does not exist.
        if (!test("[ -d $sharedPath/$dir[0] ]")) {

            // Create shared dir if it does not exist.
            run("mkdir -p $sharedPath/$dir[0]");

            // If release contains shared dir, copy that dir from release to shared.
            if (test("[ -d $(echo {{release_path}}/$dir[1]) ]")) {
                run("cp -rv {{release_path}}/$dir[1] $sharedPath/" . dirname(parse($dir[0])));
            }
        }

        // Remove from source.
        run("rm -rf {{release_path}}/$dir[1]");

        // Create path to shared dir in release dir if it does not exist.
        // Symlink will not create the path and will fail otherwise.
        run("mkdir -p `dirname {{release_path}}/$dir[1]`");

        // Symlink shared dir to release dir
        run("{{bin/symlink}} $sharedPath/$dir[0] {{release_path}}/$dir[1]");
    }

    foreach (get('shared_files_ex') as $file) {
        $dirname = dirname(parse($file[0]));
        $dirname1 = dirname(parse($file[1]));

        // Create dir of shared file if not existing
        if (!test("[ -d $sharedPath/$dirname ]")) {
            run("mkdir -p $sharedPath/$dirname");
        }

        // Check if shared file does not exist in shared.
        // and file exist in release
        if (!test("[ -f $sharedPath/$file[0] ]") && test("[ -f {{release_path}}/$file[1] ]")) {
            // Copy file in shared dir if not present
            run("cp -rv {{release_path}}/$file[1] $sharedPath/$file[0]");
        }

        // Remove from source.
        run("if [ -f $(echo {{release_path}}/$file[1]) ]; then rm -rf {{release_path}}/$file[1]; fi");

        // Ensure dir is available in release
        run("if [ ! -d $(echo {{release_path}}/$dirname1) ]; then mkdir -p {{release_path}}/$dirname1;fi");

        // Touch shared
        run("touch $sharedPath/$file[0]");

        // Symlink shared dir to release dir
        run("{{bin/symlink}} $sharedPath/$file[0] {{release_path}}/$file[1]");
    }
});


desc('Update code (extended)');
task('deploy:update_code_ex', function () {
    $repository = trim(get('repository'));
    $branch = get('branch');
    $git = get('bin/git');
    $gitCache = get('git_cache');
    $recursive = get('git_recursive', true) ? '--recursive' : '';
    $quiet = isQuiet() ? '-q' : '';
    $depth = $gitCache ? '' : '--depth 1';
    $options = [
        'tty' => get('git_tty', false),
    ];

    $at = '';
    if (!empty($branch)) {
        $at = "-b $branch";
    }

    // If option `tag` is set
    if (input()->hasOption('tag')) {
        $tag = input()->getOption('tag');
        if (!empty($tag)) {
            $at = "-b $tag";
        }
    }

    // If option `tag` is not set and option `revision` is set
    if (empty($tag) && input()->hasOption('revision')) {
        $revision = input()->getOption('revision');
        if (!empty($revision)) {
            $depth = '';
        }
    }

    // Enter deploy_path if present
    if (has('deploy_path')) {
        cd('{{deploy_path}}');
    }

    if ($gitCache && has('previous_release')) {
        try {
            // NB: 19 SEP 2019: Phil Hopper // run("$git clone $at $recursive $quiet --reference {{previous_release}} --dissociate $repository  {{release_path}} 2>&1", $options);
            run("ssh-agent sh -c 'ssh-add ~/.ssh/id_rsa; $git clone $at $recursive $quiet --reference {{previous_release}} --dissociate $repository  {{release_path}} 2>&1'", $options);
        } catch (Throwable $exception) {
            // If {{deploy_path}}/releases/{$releases[1]} has a failed git clone, is empty, shallow etc, git would throw error and give up. So we're forcing it to act without reference in this situation
            // NB: 19 SEP 2019: Phil Hopper // run("$git clone $at $recursive $quiet $repository {{release_path}} 2>&1", $options);
            run("ssh-agent sh -c 'ssh-add ~/.ssh/id_rsa; $git clone $at $recursive $quiet $repository {{release_path}} 2>&1'", $options);
        }
    } else {
        // if we're using git cache this would be identical to above code in catch - full clone. If not, it would create shallow clone.
        // NB: 19 SEP 2019: Phil Hopper // run("$git clone $at $depth $recursive $quiet $repository {{release_path}} 2>&1", $options);
        run("ssh-agent sh -c 'ssh-add ~/.ssh/id_rsa; $git clone $at $depth $recursive $quiet $repository {{release_path}} 2>&1'", $options);
    }

    if (!empty($revision)) {
        run("cd {{release_path}} && $git checkout $revision");
    }
});


desc('Unlock deploy and let Bitbucket know it failed');
task('deploy:unlock_and_fail', function () {
    run("rm -f {{deploy_path}}/.dep/deploy.lock");//always success
    print "\n\n=============\n";
    print "DEPLOY FAILED\n";
    print "=============\n\n";
    exit(1);
});


desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code_ex',
    'deploy:shared_ex',
    'deploy:writable',
    'deploy:symlink',
	'deploy:create_symlinks',
	'deploy:clear_paths',
    'deploy:unlock',
    'cleanup',
    'success'
]);


// If deploy fails automatically unlock and let bitbucket know it failed.
after('deploy:failed', 'deploy:unlock_and_fail');
