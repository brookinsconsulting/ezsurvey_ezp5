#!/usr/bin/env php
<?php
/**
 * This file is part of the eZSurvey extension.
 *
 * @copyright Copyright (C) eZ Systems AS.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

require 'autoload.php';

$fileNameDba = 'db_data.dba';
$fileNameSql = 'cleandata.sql';
$stdOutSQL = null;
$stdOutDBA = null;

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "eZ Publish SQL Survey data dump\n\n" .
                                                        "Dump sql data to file or standard output from the tables:\n" .
                                                        "  ezsurvey_group\n" .
                                                        "  ezsurvey_group_range\n" .
                                                        "  ezsurvey_registrant_range\n\n" .
                                                        "Default is file, wich will be written to:\n" .
                                                        "  kernel/classes/datatypes/ezsurvey/sql/<database>/cleandata.sql\n" .
                                                        "  kernel/classes/datatypes/ezsurvey/share/db_data.dba\n\n" .
                                                        "Script can be runned as:\n" .
                                                        "php bin/php/ezsqldumpsurveydata.php --stdout-sql\n" .
                                                        "                                  --stdout-dba\n" .
                                                        "                                  --filename-sql=customname.sql\n" .
                                                        "                                  --filename-dba=customname.dba" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "[stdout-sql][stdout-dba][filename-sql:][filename-dba:]", "",
                                array( 'stdout-sql' => "Result of sql output will be printed to standard output instead of to file.",
                                       'stdout-dba' => "Result of dba output will be printed to standard output instead of to file.",
                                       'filename-sql' => "Custom name for the sql file. Will be stored in the directory: \n" .
                                                         "kernel/classes/datatypes/ezsurvey/sql/<database>/",
                                       'filename-dba' => "Custom name for the dba file. Will be stored in the directory: \n" .
                                                         "kernel/classes/datatypes/ezsurvey/share/" ) );
$script->initialize();
$db = eZDB::instance();
$dbSchema = eZDbSchema::instance( $db );

if ( isset( $options['filename-sql'] ) )
{
    $fileNameSql = $options['filename-sql'];
}

if ( isset( $options['filename-dba'] ) )
{
    $fileNameDba = $options['filename-dba'];
}

if ( isset( $options['stdout-sql'] ) !== null )
{
    $stdOutSQL = $options['stdout-sql'];
}

if ( isset( $options['stdout-dba'] ) !== null )
{
    $stdOutDBA = $options['stdout-dba'];
}

$tableType = 'MyISAM';
if ( $db->databaseName() != "mysql" )
{
    $tableType = null;
}

$includeSchema = true;
$includeData = true;

$dbschemaParameters = array( 'schema' => $includeSchema,
                             'data' => $includeData,
                             'format' => 'generic',
                             'meta_data' => null,
                             'table_type' => $tableType,
                             'table_charset' => null,
                             'compatible_sql' => true,
                             'allow_multi_insert' => null,
                             'diff_friendly' => null,
                             'table_include' => array( 'ezsurvey',
                                                       'ezsurveyquestion',
                                                       'ezsurveyresult',
                                                       'ezsurveyquestionresult',
                                                       'ezsurveymetadata',
                                                       'ezsurveyrelatedconfig',
                                                       'ezsurveyquestionmetadata' ) );
if ( $stdOutDBA === null and $stdOutSQL === null )
{
    $path = 'extension/ezsurvey/share/' . $db->databaseName() . '/';
    $file = $path . $fileNameSql;
    $dbSchema->writeSQLSchemaFile( $file,
                                   $dbschemaParameters );
    $cli->output( 'Write "' . $file . '" to disk.' );

    $path = 'extension/ezsurvey/share/';
    $file = $path . $fileNameDba;

    // Add the table schema.
    $dbSchema->writeArraySchemaFile( $file,
                                     $dbschemaParameters );
    $cli->output( 'Write "' . $file . '" to disk.' );
}
else
{
    $filename = 'php://stdout';
    if ( $stdOutSQL !== null )
    {
        $dbSchema->writeSQLSchemaFile( $filename,
                                       $dbschemaParameters );
    }

    if ( $stdOutDBA !== null )
    {
        $dbschemaParameters['schema'] = true;
        $dbSchema->writeArraySchemaFile( $filename,
                                         $dbschemaParameters );
    }
}


$script->shutdown();
?>
