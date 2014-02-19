<?php
require '../../lib/mihimana/functions/mmFilesUtils.php';
require '../../lib/mihimana/functions/mmUrlUtils.php';

require 'lib/appCreator.php';

function form($form = null) {
    if (!isset($form)) {
        $form = array(
            'appname' => '',
            'defmod' => 'main',
            'defact' => 'index',
            'dbdriver' => 'mysql',
            'dbserv' => 'localhost',
            'dbname' => '',
            'dbuser' => '',
            'dbpasswd' => '');
    }
    ?>
    <form action="runme1st.php" method="post">
        <table>
            <tbody>
                <tr><th colspan="2">Application parameters</th></tr>
                <tr><th>App name</th><td><input type="text" name="appname" value="<?php echo $form['appname'] ?>" /></td></tr>
                <tr><th>Default module</th><td><input type="text" name="defmod" value="<?php echo $form['defmod'] ?>" /></td></tr>
                <tr><th>Default action</th><td><input type="text" name="defact" value="<?php echo $form['defact'] ?>" /></td></tr>
                <tr><th>Overwrite app</th><td><input type="checkbox" name="ovrwr" /></td></tr>
                <tr><th colspan="2">Database parameters</th></tr>
                <tr><th>Driver</th><td>
                        <select name="dbdriver">
                            <option value="fbsql" <?php echo $form['dbdriver'] == 'fbsql' ?'selected="selected"':'' ?>>FrontBase</option>
                            <option value="ibase" <?php echo $form['dbdriver'] == 'ibase' ?'selected="selected"':'' ?>>Interbase / Firebird</option>
                            <option value="mssql" <?php echo $form['dbdriver'] == 'mssql' ?'selected="selected"':'' ?>>MS SQL Server</option>
                            <option value="mysql" <?php echo $form['dbdriver'] == 'mysql' ?'selected="selected"':'' ?>>MySQL</option>
                            <option value="mysqli" <?php echo $form['dbdriver'] == 'mysqli' ?'selected="selected"':'' ?>>MySQL (mysqli)</option>
                            <option value="oci" <?php echo $form['dbdriver'] == 'oci' ?'selected="selected"':'' ?>>Oracle 7/8/9/10</option>
                            <option value="pgsql" <?php echo $form['dbdriver'] == 'pgsql' ?'selected="selected"':'' ?>>PostgreSQL</option>
                            <option value="querysim" <?php $form['dbdriver'] == 'querysim' ?'selected="selected"':'' ?>>QuerySim</option>
                            <option value="sqlite" <?php echo $form['dbdriver'] == 'sqlite' ?'selected="selected"':'' ?>>SQLite 2</option>
                        </select>
                    </td></tr>
                <tr><th>Server</th><td><input type="text" name="dbserv" value="<?php echo $form['dbserv'] ?>" /></td></tr>
                <tr><th>Db Name</th><td><input type="text" name="dbname" value="<?php echo $form['dbname'] ?>" /></td></tr>
                <tr><th>User</th><td><input type="text" name="dbuser" value="<?php echo $form['dbuser'] ?>" /></td></tr>
                <tr><th>Password</th><td><input type="text" name="dbpasswd" value="<?php echo $form['dbpasswd'] ?>" /></td></tr>
                <tr><td colspan="2"><input type="submit" name="create" value="Create application"/></td></tr>
            </tbody>
        </table>
    </form>
    <?php
}
?>



<html>
    <head>
        <title>Apps manager</title>
    </head>
    <body>
        <?php
        /*
         * 
         */
        if (count($_POST)) {    //post data exists, so it's a return from the form
            if (empty($_POST['appname']) || empty($_POST['defmod']) || empty($_POST['defact'])) { //on field is missing
                echo "Error :  one of the field is empty";
                form($_POST);
            } else { // everything is allright, do the work
                if (createApp($_POST)) { // app successfuly created
                    // do something such as redirection to db designer
                    echo "<h1>everythings done !</h1>";
                    echo "go to your app <button onclic=\"document.location.href=''\">GO !</button>";
                } else { // app creation failed => print the form
                    form($_POST);
                }
            }
        } else {
            echo "<h1>Create a new application</h1>";
            form();
        }
        ?>

    </body>
</html>

<?php
?>