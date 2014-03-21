'''
Created on 2012-5-23

@author: wangjiajun
'''

import os
import fnmatch
import re
import hashlib
import shutil
import sys

CSS_DIR = './'
DOCUMENT_ROOT = '/home/worker/snda-php/videosearch/resource/'

root = ''
dirs = []
files = []

def replaceurl(match):
    url = match.group(2)
    content = None
    if url[0:7] == 'http://':
        pass
    elif url[0:1] == '/':
        if os.path.isfile(os.path.join(DOCUMENT_ROOT, url)):
            with open(os.path.join(DOCUMENT_ROOT, url), 'rb') as fo:
                content = fo.read()
    else:
        if os.path.isfile(os.path.join(root, url)):
            with open(os.path.join(root, url), 'rb') as fo:
                content = fo.read()
    if content == None:
        return match.group(0)
    else:
        h = hashlib.md5()
        h.update(content)
        return match.group(1)+match.group(2)+'?'+h.hexdigest()+match.group(4)

if __name__ == '__main__':
    pattern = re.compile(r'(url\s*\(\s*[\'"]?\s*)([^\'"\s?]+)(\?[^\'"\s]*)?(\s*[\'"]?\s*\))', re.IGNORECASE)
    for root, dirs, files in os.walk(CSS_DIR):
        for file in files:
            if fnmatch.fnmatch(file, '*.css'):
                print('begin process file "'+os.path.join(root, file)+'" ...')
                with open(os.path.join(root, file), 'r+', newline='') as fo:
                    lines = []
                    for line in fo.readlines():
                        if pattern.search(line):
                            print('original: ', line, end='')
                            line = pattern.sub(replaceurl, line)
                            print('replaced: ', line, end='')
                        lines.append(line)
                    src = os.path.join(root, file)
                    dst = os.path.join(root, file+'.bak')
                    shutil.copyfile(src, dst)
                    fo.seek(0)
                    fo.truncate()
                    fo.write(''.join(lines))
