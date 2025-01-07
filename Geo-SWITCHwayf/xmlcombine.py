#!/usr/bin/python
import sys
from lxml import etree

def run(files):
    namespaces = {}
    final_tree = None

    namespaces = {'md': 'urn:oasis:names:tc:SAML:2.0:metadata'}
    base = etree.parse(files[0])

    root=base.getroot()
    parent = None
    for ed in root.findall('.//md:EntityDescriptor', namespaces):
        parent = ed.getparent()
        if parent is not None:
            parent.remove(ed)

    base.write('root.xml', pretty_print=True, xml_declaration=True, encoding='UTF-8')

    for filename in files:
        current_file_base = etree.parse(filename)
        current_root = current_file_base.getroot()

        for ed in current_root.findall('.//md:EntityDescriptor', namespaces):
            parent.append(ed)

    base.write('/var/www/html/switchwayf/tmp/metadata.xml', pretty_print=True, xml_declaration=True, encoding='UTF-8')
if __name__ == "__main__":
    run(sys.argv[1:])
