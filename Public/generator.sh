#!/bin/bash
# ./generator.sh codePath

result=0

if [ $# != 1 ] ; then
	result=500
    echo $result
	exit 1;
fi

cd $1

codeFilePath_C="generateCode.c"
codeFilePath_CPP="generateCode.cpp"
programName="./generateCode"
examListFile="examList.txt"

if [ -f "$codeFilePath_C" ]; then
    gcc $codeFilePath_C -w -o $programName && $programName > $examListFile
    result=$?
elif [ -f "$codeFilePath_CPP" ]; then
    g++ $codeFilePath_CPP -w -o $programName && $programName > $examListFile
    result=$?
else
    result=404 # file not found
fi

echo $result
