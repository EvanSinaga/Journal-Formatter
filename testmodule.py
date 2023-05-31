import sys
# import docx


modulename = 'docx'
if modulename not in sys.modules:
    print('You have not imported the {} module'.format(modulename))
else:
    print("Imported")
