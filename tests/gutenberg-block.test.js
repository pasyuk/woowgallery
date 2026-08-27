"use strict";

const assert = require("node:assert/strict");
const test = require("node:test");

const block = require("../assets/js/src/blocks.js");

function createPreviewFixture() {
  const appendedScripts = [];
  const initializedIds = [];
  const deletedIds = [];
  const parentInitializedIds = [];
  const manager = {
    searchAndInit(id) {
      initializedIds.push(id);
    },
    deleteGalleryById(id) {
      deletedIds.push(id);
    },
  };
  const parentManager = {
    searchAndInit(id) {
      parentInitializedIds.push(id);
    },
  };
  const frameWindow = {};
  const document = {
    defaultView: frameWindow,
    createElement(tagName) {
      assert.equal(tagName, "script");
      return {
        async: true,
        parentNode: null,
        setAttribute() {},
      };
    },
    body: {
      appendChild(script) {
        script.parentNode = this;
        appendedScripts.push(script.src);
        queueMicrotask(() => {
          if (script.src.endsWith("/amron.js")) {
            frameWindow.WoowGallery.skins.amron = manager;
          }
          script.onload();
        });
      },
    },
  };
  const wrapper = { id: "woowgallery-12468" };
  const root = {
    ownerDocument: document,
    querySelector(selector) {
      assert.equal(selector, ".woowgallery-wrapper");
      return wrapper;
    },
  };
  const parentWindow = {
    WoowGallery: {
      sharedConfig: true,
      skins: { amron: parentManager },
      galleries: { parent: true },
    },
  };
  const gallery = {
    skin: {
      slug: "amron",
      info: {
        scripts: [
          "https://wp-dev.loc/amron.js",
          "https://wp-dev.loc/woowlightbox.js",
        ],
      },
    },
  };

  return {
    appendedScripts,
    deletedIds,
    frameWindow,
    gallery,
    initializedIds,
    parentInitializedIds,
    parentWindow,
    root,
  };
}

test("interactive preview loads and initializes inside the block ownerDocument", async () => {
  const fixture = createPreviewFixture();
  const preview = await block.initializePreview(
    fixture.root,
    fixture.gallery,
    fixture.parentWindow,
  );

  assert.deepEqual(fixture.appendedScripts, [
    "https://wp-dev.loc/amron.js",
    "https://wp-dev.loc/woowlightbox.js",
  ]);
  assert.deepEqual(fixture.initializedIds, ["woowgallery-12468"]);
  assert.deepEqual(fixture.parentInitializedIds, []);
  assert.equal(fixture.frameWindow.WoowGallery.sharedConfig, true);
  assert.notEqual(
    fixture.frameWindow.WoowGallery,
    fixture.parentWindow.WoowGallery,
  );
  assert.deepEqual(fixture.frameWindow.WoowGallery.galleries, {});

  preview.destroy();

  assert.deepEqual(fixture.deletedIds, ["woowgallery-12468"]);
});

test("multiple previews reuse scripts within one editor iframe", async () => {
  const fixture = createPreviewFixture();
  const secondRoot = {
    ownerDocument: fixture.root.ownerDocument,
    querySelector() {
      return { id: "woowgallery-12469" };
    },
  };

  await block.initializePreview(
    fixture.root,
    fixture.gallery,
    fixture.parentWindow,
  );
  await block.initializePreview(
    secondRoot,
    fixture.gallery,
    fixture.parentWindow,
  );

  assert.deepEqual(fixture.appendedScripts, [
    "https://wp-dev.loc/amron.js",
    "https://wp-dev.loc/woowlightbox.js",
  ]);
  assert.deepEqual(fixture.initializedIds, [
    "woowgallery-12468",
    "woowgallery-12469",
  ]);
});

test("shortcode serialization preserves the legacy saved content", () => {
  assert.equal(
    block.serializeShortcode({
      gallery: { id: 12468, subtype: "woowgallery" },
    }),
    '[woowgallery id="12468"]',
  );
  assert.equal(block.serializeShortcode({ gallery: undefined }), "");
});
