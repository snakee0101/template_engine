<?php

namespace Blade\Core;

/**
 * Maps views to corresponding compiled path
 * $base_directory path starts in root directory of a project (to access views folder, you must write "/views")
 */
class ViewsMapper
{
    private $file_paths = [];
    private $base_directory, $compiled_directory;

    public function __construct($base_directory = null)
    {
        $e = new TemplateEngine();
        $e->loadConfig();

        $base_directory ??= $e->config('views_directory');  //if views directory is not set, it is taken from config file
        $this->base_directory = __DIR__ . "/.." . $base_directory;    //convert path to base directory to absolute

        //generate compiled directory sub path
        $views_offset = strpos($this->base_directory, '/views');
        $directory_sub_path = substr($this->base_directory, $views_offset); //normalize path name - truncate everything until /views/some_directory
        $compiled_directory_sub_path = str_replace($e->config('views_directory'), $e->config('compiled_path'), $directory_sub_path);

        $original_compiled_directory = __DIR__ . "/.." . $e->config('compiled_path');

        $this->compiled_directory = str_replace($e->config('compiled_path'), $compiled_directory_sub_path, $original_compiled_directory);   //  /compiled => /compiled/sub/sub2
    }

    public function run()
    {
        $directory = dir($this->base_directory); //open the directory as an object

        if( $directory === false)
            return; //if directory is empty - then there is nothing to search

        while ( false !== ($filename = $directory->read()) ) //get all file paths in this directory
        {            
            //if there is a directory - recursively find files there and merge the output
            if( is_dir($directory_path = $this->base_directory . "/" . $filename) 
                && $filename != '.' 
                && $filename != '..') //ignore directories, that end with "." or ".."
            {
                $views_offset = strpos($directory_path, '/views');
                $directory_path_ = substr($directory_path, $views_offset); //normalize path name - truncate everything until /views/some_directory
                
                $this->file_paths = array_merge( $this->file_paths, (array)(new self($directory_path_))->run() );
            }
            
            //filter out only the valid views - the files with .blade.php extension
            if( str_ends_with($filename, '.blade.php') === false )
                continue;

            $this->file_paths["{$this->base_directory}/$filename"] = "{$this->compiled_directory}/$filename";
        }

        return $this->file_paths;
    }
}