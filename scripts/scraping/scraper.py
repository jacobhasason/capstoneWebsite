# -*- coding: utf-8 -*-
"""
Created on Thu May  7 20:38:18 2026

@author: isaka
"""

# Program to scrape Abstract and Citations from docx and pdf files
# Currently only works for text based document (no pdf scans)


import sys
import json
import re
import fitz
from docx import Document
from pathlib import Path

def extract_pdf(path):
    text = ""

    with fitz.open(path) as pdf:
        for page in pdf:
            text += page.get_text()

    return text


def extract_docx(path):
    doc = Document(path)
    return "\n".join([p.text for p in doc.paragraphs])



def get_abstract(text):
    match = re.search(
        r'abstract\s*(.*?)(?=\bresults\b|\bintroduction\b|\bkeywords\b|\bcitations\b|\breferences\b)',
        text,
        re.IGNORECASE | re.DOTALL
    )

    if match:
        return match.group(1).strip()

    return ""



def get_citations(text):
    match = re.search(
        r'(citations|references|works cited|bibliography)\s*(.*)',
        text,
        re.IGNORECASE | re.DOTALL
    )

    if match:
        citations_text = match.group(2).strip()

        citations = re.split(r'\n\s*\n', citations_text)

        return [c.strip() for c in citations if c.strip()]

    return []



def main():

    #print("Python connected successfully")

    if len(sys.argv) < 3:
        print("ERROR: File path and original filename must be passed to Python.")
        sys.exit(1)

    tmp_path = sys.argv[1]
    original_name = sys.argv[2]

    suffix = Path(original_name).suffix.lower()
    '''
    print("Uploaded file:")
    print(tmp_path)
    print("Original filename:")
    print(original_name)
    print("Detected suffix:")
    print(suffix)
    '''

    text = ""

    if suffix == ".pdf":
        text = extract_pdf(tmp_path)

    elif suffix == ".docx":
        text = extract_docx(tmp_path)

    else:
        print(f"Unsupported file type: {suffix}")
    
    '''
    print("TEXT LENGTH:", len(text))
    print("TEXT PREVIEW:")
    print(text[:1000])
    '''
    
    result = {
        "abstract": get_abstract(text),
        "citations": get_citations(text)
    }
    
    print(json.dumps(result))
    
if __name__ == "__main__":
    main()