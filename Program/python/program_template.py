try:
    import sys
    input_len = len(sys.argv)-1
    raise Exception()
    if input_len>0:
        # handle input
        pass
    else:
        # handle non-input
        pass
except Exception as e:
    import json
    errors = {'ReturnCode': '417', 'ReturnString': '程序出错', 'ShowMessage': '', 'Data': ''}
    print(json.dumps(errors, ensure_ascii=False))