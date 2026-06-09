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
import requests
import fitz
from bs4 import BeautifulSoup
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

def extract_webpage(url):
    response = requests.get(
        url,
        timeout=15,
        headers={
            "User-Agent": "Mozilla/5.0"
        }
    )
    response.raise_for_status()

    soup = BeautifulSoup(response.text, "html.parser")

    for tag in soup(["script", "style", "nav", "footer", "header"]):
        tag.decompose()

    # Try common abstract meta tags first
    abstract_meta = soup.find("meta", attrs={"name": "description"})
    if abstract_meta and abstract_meta.get("content"):
        meta_text = abstract_meta["content"].strip()
    else:
        meta_text = ""

    page_text = soup.get_text("\n", strip=True)

    return meta_text + "\n" + page_text



def main():

    if len(sys.argv) < 3:
        print(json.dumps({
            "abstract": "",
            "citations": [],
            "error": "Missing source or source type"
        }))
        sys.exit(1)

    source = sys.argv[1]
    source_type = sys.argv[2]

    text = ""

    if source_type == "url":
        text = extract_webpage(source)

    else:
        tmp_path = source
        original_name = source_type
        suffix = Path(original_name).suffix.lower()

        if suffix == ".pdf":
            text = extract_pdf(tmp_path)

        elif suffix == ".docx":
            text = extract_docx(tmp_path)

    result = {
        "abstract": get_abstract(text),
        "citations": get_citations(text)
    }

    print(json.dumps(result))
    
if __name__ == "__main__":
    main()