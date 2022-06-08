# -*- coding: utf-8 -*-
import os
import tarfile
import zipfile
import argparse
import json


def get_php_modules(dir, modelus_list):
    php_modules = []
    php_list = modelus_list.split("|")
    for module_name in php_list:
        php_path = os.path.join(dir, module_name)
        if os.path.exists(php_path):
            php_modules.append(php_path)

    return php_modules


if __name__ == "__main__":
    ap = argparse.ArgumentParser(description="打包插件")
    ap.add_argument("-d", "--dir", required=False, help="自定义插件源文件所在目录")
    ap.add_argument("-m",
                    "--modules",
                    required=False,
                    help="自定义所需要打包的模块，多个时以|分隔")
    ap.add_argument("-z", "--zip", required=False, action="store_true", help="打包生成的插件")

    args = vars(ap.parse_args())
    build_dir = "build"
    print("************************************************")
    dir_path = os.path.join(os.getcwd(), "src")
    if args["dir"] and os.path.exists(args["dir"]):
        dir_path = args["dir"]
    print("INFO:: 源文件路径: " + dir_path)
    work_path = os.path.abspath(os.path.dirname(dir_path))
    print("INFO:: 工作路径: " + work_path)

    build_path = os.path.join(work_path, build_dir)
    if not os.path.exists(build_path):
        os.makedirs(build_path)

    info_file = os.path.join(dir_path, "INFO")
    if not os.path.exists(info_file):
        print("ERROR:: 目录下没有找到需要打包的定义文件！")
        exit()

    php_modules = []
    if args["modules"]:
        php_modules = get_php_modules(dir_path, args["modules"])
    else:
        for root, _dirs, files in os.walk(dir_path):
            for filename in files:
                read_file = os.path.join(root, filename)
                read_ext = os.path.splitext(read_file)[1]
                if read_ext in [".php"]:
                    php_modules.append(read_file)

    if len(php_modules) == 0:
        print("ERROR:: 目录下没有找到需要打包的模块文件！")
        exit()

    info = {"name": "unknow", "version": "0.1"}
    with open(info_file, encoding="utf-8") as load_f:
        info = json.load(load_f)

    build_file = os.path.join(build_path, info["name"] + "_v" + info["version"] + ".aum")
    print("INFO:: 插件名称: " + info["name"])
    print("INFO:: 插件版本: " + info["version"])

    if os.path.exists(build_file):
        os.remove(build_file)

    with tarfile.open(build_file, mode="w:gz") as tar:
        tar.add(info_file, arcname=os.path.basename(info_file))
        print("INFO:: 打包: " + info_file)
        for php_file in php_modules:
            tar.add(php_file, arcname=os.path.basename(php_file))
            print("INFO:: 打包: " + php_file)
        tar.close()
    print("INFO:: 已经生成插件至: " + build_file)

    if args["zip"]:
        zip_file = os.path.join(build_path, info["name"] + "_v" + info["version"] + ".zip")
        if (os.path.exists(zip_file)):
            os.remove(zip_file)
        with zipfile.ZipFile(zip_file, mode="w", compression=zipfile.ZIP_STORED) as zf:
            zf.write(build_file, arcname=os.path.basename(build_file))
        print("INFO:: 已经打包插件至: " + zip_file)

    print("SUCCESS:: 打包操作已经完成。")
    print("************************************************")
