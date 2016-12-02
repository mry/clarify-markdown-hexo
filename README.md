# clarify-markdown-hexo
Modified Clarify script for markdown export tailored for Hexo

##Allow categories to be used
Merged pull request from [rastographics](https://github.com/rastographics)
This update optionally allows for a user-defined prefix to be specified when creating tags in Clarify, for the purpose of using the tags to make categories in Hexo.

Sample of @article.md:
```
print printHexoAttributesForArticle($article->tags,'categories','cat:'); //this prints only tags with "cat:" prefix as categories
print printHexoAttributesForArticle($article->tags,'tags','','cat:'); //this excludes any tags with "cat:" prefix since they are used for categories instead

```
