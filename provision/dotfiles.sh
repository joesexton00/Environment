#!/usr/bin/env bash

cd "$(dirname "${BASH_SOURCE}")";

function provisionDotFiles() {

    local bin_dir="$(myrealpath $(pwd)/../bin)";
    local dotfiles_dir="$(myrealpath $(pwd)/../dotfiles)";

    for file in .{aliases,bash_profile,bash_prompt,bashrc,exports,extra,functions,gitconfig,inputrc,wgetrc}; do
        if [ -L ~/$file ]; then
            echo "Unlinking ~/$file"
            unlink ~/$file;
        elif [ -f ~/$file ]; then
            echo "Removing ~/$file"
            rm ~/$file;
        fi

        echo "Linking ~/$file to $dotfiles_dir/$file"
        ln -s $dotfiles_dir/$file ~/$file
    done;

    unset file;

    if [ -L ~/bin ]; then
        echo "Unlinking ~/bin"
        unlink ~/bin;
    elif [ -f ~/bin ]; then
        echo "Removing ~/bin"
        rm ~/bin;
    fi
    ln -s $bin_dir ~/bin

    source ~/.bash_profile;
}

myrealpath() {
  case "${1}" in
    [./]*)
    echo "$(cd ${1%/*}; pwd)/${1##*/}"
    ;;
    *)
    echo "${PWD}/${1}"
    ;;
  esac
}


if [ "$1" == "--force" -o "$1" == "-f" ]; then
    provisionDotFiles;
else
    read -p "This may overwrite existing files in your home directory. Are you sure? (y/n) " -n 1;
    echo "";
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        provisionDotFiles;
    fi;
fi;
unset doIt;
