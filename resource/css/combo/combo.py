# -*- coding: utf-8 -*-

import os, fnmatch, re, errno, shutil, hashlib, string, sys, time, random

def listfiles(root, patterns='*', recurse=1, return_folders=0):        
    pattern_list = patterns.split(';')

    class Bunch:
        def __init__(self, **kwds): self.__dict__.update(kwds)
    arg = Bunch(recurse=recurse, pattern_list=pattern_list,
        return_folders=return_folders, results=[])

    def visit(arg, dirname, files):
        for name in files:
            fullname = os.path.normpath(os.path.join(dirname, name))
            if arg.return_folders or os.path.isfile(fullname):
                for pattern in arg.pattern_list:
                    if fnmatch.fnmatch(name, pattern):
                        arg.results.append(fullname)
                        break
                    
        if not arg.recurse: files[:]=[]

    os.path.walk(root, visit, arg)
    return arg.results

def combo_css(path) :
    cssfiles = listfiles(path, '*.css')
    for css in cssfiles:
        reader = open(css, 'r')
        contents = []
        for line in reader.xreadlines() :
            p = re.compile('@import\s*url\([\"\']?\.{2}/(.*css)',re.IGNORECASE)
            m = p.match(line)
            if m :
                res_file = m.group(1)
                file_path = path + res_file
                if os.path.exists(file_path) :
                    file_reader = open(file_path, 'r')
                    content = file_reader.read()
                    strinfo = re.compile('@charset.*;')
                    content2 = strinfo.sub('',content)
                    contents.append('/*import from ' + file_path+',(by build.py)*/\n\n')
                    contents.append(content2)
                else :
                    print 'in file '+ css +',' + file_path + ' not exists'
        reader.close()

        if len(contents):
            writer = open(css.replace('.css', '.combo.css'), 'w')
            writer.writelines(contents)
            writer.close()
            print css + ' combo success'

def main():
    path = "../";
    combo_css(path)

if __name__ == '__main__':
    main()
