
document.addEventListener("DOMContentLoaded", () => {

    const topics = window.topicData || [];

    const mainTopicSelect =
        document.getElementById("mainTopicSelect");

    const subtopicSelect =
        document.getElementById("subtopicSelect");

    if (!mainTopicSelect || !subtopicSelect) return;

    mainTopicSelect.addEventListener("change", () => {

        const selectedMainTopic =
            mainTopicSelect.value;

        // reset subtopics
        subtopicSelect.innerHTML =
            '<option value="">Select Subtopic</option>';

        // matching subtopics
        const matchingTopics = topics.filter(topic =>
            topic.category_id == selectedMainTopic
        );

        matchingTopics.forEach(topic => {

            const option =
                document.createElement("option");

            option.value =
                topic.topic_name;

            option.textContent =
                topic.topic_name;

            subtopicSelect.appendChild(option);

        });

    });

});

