const newsListBtnLoadMore = document.getElementById('newsListBtnLoadMore');

if (newsListBtnLoadMore) {
  const newsListList = document.getElementById('newsListList');
  const newsListPaginationWrapper = document.getElementById('newsListPaginationWrapper');
  const newsListBtnLoadMoreCount = document.getElementById('newsListBtnLoadMoreCount');

  const params = new URLSearchParams(window.location.search);
  let page = +params.get('page') || 1;

  const addItem = el => {
    const item = document.createElement('li');
    item.classList.add('news-list-item');

    const link = document.createElement('a');
    link.classList.add('news-list-item__link');
    link.href = el.url;
    item.appendChild(link);

    const img = document.createElement('img');
    img.classList.add('news-list-item__img');
    img.src = el.image;
    link.appendChild(img);

    const content = document.createElement('div');
    content.classList.add('news-list-item__content');
    item.appendChild(content);

    const date = document.createElement('span');
    date.classList.add('news-list-item__date');
    content.appendChild(date);

    const xmlns = 'http://www.w3.org/2000/svg';
    const xlink = 'http://www.w3.org/1999/xlink';
    const dateImg = document.createElementNS(xmlns, 'svg');
    dateImg.classList.add('news-list-item__date-img');
    date.appendChild(dateImg);

    const dateImgUse = document.createElementNS(xmlns, 'use');
    dateImgUse.setAttributeNS(xlink, 'href', '/resourse/images/shared-sprite-icons.svg#icon-calendar');
    dateImg.appendChild(dateImgUse);

    date.appendChild(document.createTextNode(el.date));

    const title = document.createElement('a');
    title.classList.add('news-list-item__title');
    title.href = el.url;
    title.textContent = el.title;
    content.appendChild(title);

    const text = document.createElement('div');
    text.classList.add('news-list-item__text');
    text.textContent = el.content;
    content.appendChild(text);

    newsListList.appendChild(item);
  };

  const onClickNewsListBtnLoadMore = async () => {
    newsListBtnLoadMore.disabled = true;
    try {
      const url = '/index.php?route=news_list/loadMore';
      const body = JSON.stringify({ page: page + 1 });
      const response = await fetch(url, { method: 'POST', body });
      const news = await response.json();

      news.items.forEach(addItem);

      newsListPaginationWrapper.innerHTML = news.pagination;
      if (news.countLoadItems) {
        newsListBtnLoadMoreCount.textContent = news.countLoadItems;
        newsListBtnLoadMore.disabled = false;
      } else {
        newsListBtnLoadMore.remove();
      }

      ++page;
    } catch (err) {
      console.error(err.message);
    }
  };

  newsListBtnLoadMore.addEventListener('click', onClickNewsListBtnLoadMore);
}
